<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Helper\ApiException;
use App\Helper\ApiResponse;
use App\Helper\TimeCalc;
use App\Helper\Validator;
use App\Repository\GroupRepository;
use App\Repository\TaskRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tasks", name="task_")
 */
class TasksController extends AbstractController
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index()
    {
        $tasks = $this->getRepository()->findAllIsset();
        return new ApiResponse("", $tasks);
    }

    /**
     * @Route("/get/{id}", name="get", methods={"GET"})
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function get(string $id)
    {
        Validator::checkEmptyData($id, 'id');
        Validator::checkNum($id, 'id');
        $task = $this->getRepository()->find((int)$id);
        Validator::checkEmptyObject($task, 'Group');
        if ($task->getStatus() === Task::STATUS_DELETED) {
            throw new ApiException(ApiResponse::HTTP_GONE, "The task list has been deleted ");
        }
        return new ApiResponse("", $task);
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        Validator::checkIssetData($data, 'name');
        Validator::checkIssetData($data, 'eta');
        Validator::checkEmptyData($data['name'], 'name');
        Validator::checkEmptyData($data['eta'], 'eta');

        $task = new Task();
        $this->update($task, $data);

        $parentGroup = $this->getRepositoryGroup()->find((int)$data['groupId']);

        Validator::checkEmptyObject($parentGroup, 'ParentGroup');
        Validator::checkTime($task->getEta(), $parentGroup->getLeftEta());

        $em = $this->getDoctrine()->getManager();

        $parentGroup->setLeftEta(
            TimeCalc::normalizeTime(
                TimeCalc::removeTime($parentGroup->getLeftEta(), $task->getEta())
            )
        );

        $this->checkTime($em, $parentGroup, $task->getEta());
        $task->setParentGroup($parentGroup);

        $em->persist($task);
        $em->flush();
        return new ApiResponse('', $task, [], ApiResponse::HTTP_CREATED);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"PUT"})
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function edit(Request $request, string $id)
    {
        $data = json_decode($request->getContent(), true);

        Validator::checkEmptyData($id, 'id');
        Validator::checkNum($id, 'id');
        Validator::checkIssetData($data, 'name');
        Validator::checkIssetData($data, 'eta');
        Validator::checkIssetData($data, 'status');
        Validator::checkEmptyData($data['name'], 'name');
        Validator::checkEmptyData($data['eta'], 'eta');
        Validator::checkEmptyData($data['status'], 'status');

        $task = $this->getRepository()->find((int)$id);

        Validator::checkEmptyObject($task, 'Task');

        if ($data['status'] !== Task::STATUS_NEW) {
            throw new ApiException(ApiResponse::HTTP_CONFLICT, "You can not change the status to new");
        } elseif ($task->getStatus() === Task::STATUS_DONE && $data['status'] !== Task::STATUS_DONE) {
            throw new ApiException(ApiResponse::HTTP_CONFLICT, "You can not change the status. Status is Done");
        }

        $diffEta = TimeCalc::strTimeToSecond($data['eta']) - $task->getEta();

        if ($task->getStatus() === Task::STATUS_ACTIVE) {
            $working = TimeCalc::strTimeToSecond(
                TimeCalc::normalizeTimestamp(
                $task->getUpdatedAt()->getTimestamp() - (new \DateTime())->getTimestamp()
                )
            );
            $task->setSpend($task->getSpend() + $working);
        } else {
            $working = 0;
        }

        $task->setStatus(Task::CHOICES_STATUS[$data['status']]);
        $this->update($task, $data);

        $em = $this->getDoctrine()->getManager();
        /**
         * @var Group $parentGroupCurrent
         */
        $parentGroupCurrent = $task->getParentGroup();
        if ($parentGroupCurrent->getId() === (int)$data['groupId']) {
            Validator::checkTime($parentGroupCurrent->getLeftEta(), $diffEta);
            $parentGroupCurrent->setLeftEta($parentGroupCurrent->getLeftEta() - $diffEta);
            $parentGroupCurrent->setSpend($parentGroupCurrent->getSpend() + $working);
            $this->checkTime($em, $parentGroupCurrent);
        } else {
            $parentGroup = $this->getRepositoryGroup()->find((int)$data['groupId']);
            Validator::checkEmptyObject($parentGroup, 'ParentGroup');
            Validator::checkTime($task->getEta(), $parentGroup->getLeftEta());

            $parentGroup->setLeftEta($parentGroup->getLeftEta() - $task->getEta());

            $parentGroup->setSpend($parentGroup->getSpend() + $task->getSpend());
            $task->setParentGroup($parentGroup);

            $parentGroupCurrent->setLeftEta($parentGroupCurrent->getLeftEta() + $task->getEta());

            $parentGroupCurrent->setSpend($parentGroupCurrent->getSpend() - $task->getSpend());
            $this->checkTime($em, $parentGroupCurrent, $diffEta, $working);
            $this->checkTime($em, $parentGroup, $diffEta, $working);
            $em->persist($parentGroup);
        }
        $em->persist($parentGroupCurrent);

        $em->persist($task);
        $em->flush();

        return new ApiResponse('', null, [], ApiResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete(int $id)
    {
        Validator::checkEmptyData($id, 'id');
        Validator::checkNum($id, 'id');

        $task = $this->getRepository()->find((int)$id);

        Validator::checkEmptyObject($task, 'Task');

        $task->setStatus(Task::STATUS_DELETED);

        $em = $this->getDoctrine()->getManager();

        $this->checkTime(
            $em,
            $task->getParentGroup(),
            0 - $task->getEta(),
            0 -$task->getSpend()
        );

        $task->setParentGroup(null);

        $em->persist($task);
        $em->flush();

        return new ApiResponse('', null, [], ApiResponse::HTTP_NO_CONTENT);
    }

    /**
     * @return TaskRepository
     */
    private function getRepository(): TaskRepository
    {
        return $this->getDoctrine()->getRepository(Task::class);
    }

    /**
     * @return GroupRepository
     */
    private function getRepositoryGroup(): GroupRepository
    {
        return $this->getDoctrine()->getRepository(Group::class);
    }

    private function update(Task $task, array $data)
    {
        $task->setName($data['name']);
        $task->setEta($data['eta']);
        $task->setDescription($data['description'] ?? '');

        Validator::checkIssetData($data, 'groupId');
        Validator::checkEmptyData($data['groupId'], 'groupId');
        Validator::checkNum($data['groupId'], 'groupId');
    }

    protected function checkTime(ObjectManager $em, Group $group, $diffEta = '0:0:0', $diffSpend = '0:0:0', $groupParentId = 0, $taskId = 0)
    {
        if ($group->getTaskList()) {
            /**
             * @var TaskList $taskListCurrent
             */
            $taskListCurrent = $group->getTaskList();
            Validator::checkTime($taskListCurrent->getLeftEta(), $diffEta);
            $taskListCurrent->setLeftEta(
                TimeCalc::normalizeTime(
                    TimeCalc::removeTime($taskListCurrent->getLeftEta(), $diffEta)
                )
            );
            $taskListCurrent->setSpend(TimeCalc::normalizeTime(
                TimeCalc::addTime($taskListCurrent->getLeftEta(), $diffSpend)
            ));
            $em->persist($taskListCurrent);
            $group->setTaskList($taskListCurrent);
        } else {
            /**
             * @var Group $parentGroupCurrent
             */
            $parentGroupCurrent = $group->getParentGroup();
            Validator::checkTime($parentGroupCurrent->getLeftEta(), $diffEta);

            $parentGroupCurrent->setLeftEta(
                TimeCalc::normalizeTime(
                    TimeCalc::removeTime($parentGroupCurrent->getLeftEta(), $diffEta)
                )
            );
            $parentGroupCurrent->setSpend(TimeCalc::normalizeTime(
                TimeCalc::addTime($parentGroupCurrent->getLeftEta(), $diffSpend)
            ));
            $this->checkTime($em, $parentGroupCurrent);
            $em->persist($group);
        }
    }
}
