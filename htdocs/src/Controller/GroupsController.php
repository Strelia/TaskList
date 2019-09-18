<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\TaskList;
use App\Helper\ApiException;
use App\Helper\ApiResponse;
use App\Helper\TimeCalc;
use App\Helper\Validator;
use App\Repository\GroupRepository;
use App\Repository\TaskListRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/groups", name="groups_")
 */
class GroupsController extends AbstractController
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index()
    {
        $groups = $this->getRepository()->findAllIsset();
        return new ApiResponse('', $groups);
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

        $group = $this->getRepository()->find((int)$id);
        Validator::checkEmptyObject($group, 'Group');
        if ($group->getStatus() === Group::STATUS_DELETED) {
            throw new ApiException(ApiResponse::HTTP_GONE, "The task list has been deleted ");
        }

        return new ApiResponse('', $group);
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

        $group = new Group();
        $group->setLeftEta($data['eta']);
        $this->update($group, $data);

        if (empty($group->getTaskList()) && empty($group->getParentGroup())) {
            Validator::checkCondition(
                (!isset($data['taskListId']) || !$data['taskListId']) &&
                (!isset($data['groupId']) || !$data['groupId']),
                "",
                [
                    "fields" => "Field taskListId or groupId is not be empty",
                ]
            );
        }
        $em = $this->getDoctrine()->getManager();
        if (isset($data['taskListId'])) {
            Validator::checkNum($data['taskListId'], 'taskListId');
            $this->checkTime($em, $group, TimeCalc::strTimeToSecond($data['eta']), $data['taskListId']);
        } elseif (isset($data['groupId'])) {
            Validator::checkNum($data['groupId'], 'groupId');
            $this->checkTime($em, $group, TimeCalc::strTimeToSecond($data['eta']), $data['groupId']);
        }

        $em->flush();
        return new ApiResponse('', $group, [], ApiResponse::HTTP_CREATED);
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
        Validator::checkEmptyData($data['name'], 'name');
        Validator::checkEmptyData($data['eta'], 'eta');

        $group = $this->getRepository()->find((int)$id);
        Validator::checkEmptyObject($group, 'Group');
        $diffEta = TimeCalc::strTimeToSecond($data['eta']) - $group->getEta();
        $this->update($group, $data);

        $em = $this->getDoctrine()->getManager();
        if ($group->getTaskList()) {
            Validator::checkIssetData($data, 'taskListId');
            Validator::checkEmptyData($data['taskListId'], 'taskListId');
            Validator::checkNum($data['taskListId'], 'taskListId');
            $this->checkTime($em, $group, $diffEta, (int)$data['taskListId']);
        } elseif ($group->getParentGroup()) {
            Validator::checkIssetData($data, 'groupId');
            Validator::checkEmptyData($data['groupId'], 'groupId');
            Validator::checkNum($data['groupId'], 'groupId');
            $this->checkTime($em, $group, (int)$data['groupId'], $diffEta);
        }

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

        /**
         * @var Group $group
         */
        $group = $this->getRepository()->findForDelete((int)$id);
        Validator::checkEmptyObject($group, 'Group');
        if (!$group->getTasks()->isEmpty()) {
            throw new ApiException(ApiResponse::HTTP_CONFLICT, "Group has Task");
        }
        $group->setStatus(Group::STATUS_DELETED);

        $em = $this->getDoctrine()->getManager();

        $this->checkTime($em, $group, $group->getEta(), $group->getSpend());

        $this->checkTime(
            $em,
            $group,
            0 - $group->getEta(),
            0 - $group->getSpend()
        );
        $em->flush();
        return new ApiResponse('', null, [], ApiResponse::HTTP_NO_CONTENT);
    }

    /**
     * @return GroupRepository
     */
    private function getRepository(): GroupRepository
    {
        return $this->getDoctrine()->getRepository(Group::class);
    }

    /**
     * @return TaskListRepository
     */
    private function getRepositoryTaskList(): TaskListRepository
    {
        return $this->getDoctrine()->getRepository(TaskList::class);
    }

    private function update(Group $group, array $data)
    {
        $group->setName($data['name']);
        $group->setEta($data['eta']);
        $group->setLeftEta(
            TimeCalc::normalizeTime(
                TimeCalc::removeTime($data['eta'], $group->getLeftEta())
            )
        );
        $group->setDescription($data['description'] ?? '');
    }

    protected function checkTime(ObjectManager $em, Group $group, $diffEta = 0, $groupParentId = 0, $taskId = 0) {
        if ($group->getTaskList()) {
            /**
             * @var TaskList $taskListCurrent
             */
            $taskListCurrent = $group->getTaskList();
            if ($taskListCurrent->getId() !== $taskId) {
                $taskListNew = $this->getRepositoryTaskList()->find($taskId);
                Validator::checkEmptyObject($taskListNew, 'New Task List');
                Validator::checkTime($taskListNew->getLeftEta(), $group->getEta());
                $taskListNew->setLeftEta(
                    $taskListNew->getLeftEta() - $group->getLeftEta()
                );
                $taskListNew->setSpend($taskListNew->getLeftEta() + $group->getLeftEta());
                $em->persist($taskListNew);
                $group->setTaskList($taskListNew);

                $taskListCurrent->setLeftEta($taskListCurrent->getLeftEta() + $group->getLeftEta());
                $em->persist($taskListCurrent);
            } else {
                Validator::checkTime($taskListCurrent->getLeftEta(), $diffEta);
                $taskListCurrent->setLeftEta($taskListCurrent->getLeftEta() - $diffEta);
                $em->persist($taskListCurrent);
                $group->setTaskList($taskListCurrent);
            }
        } else {
            /**
             * @var Group $parentGroupCurrent
             */
            $parentGroupCurrent = $group->getParentGroup();
            if ($parentGroupCurrent->getId() !== $parentGroupCurrent) {

                $parentGroupNew = $this->getRepository()->find($groupParentId);
                Validator::checkEmptyObject($parentGroupNew, 'New Task List');
                Validator::checkTime($parentGroupNew->getLeftEta(), $group->getEta());

                $parentGroupNew->setLeftEta($parentGroupNew->getLeftEta() - $group->getLeftEta());

                $parentGroupCurrent->setLeftEta($parentGroupCurrent->getLeftEta() + $group->getLeftEta());

                $group->setParentGroup($parentGroupNew);

                $this->checkTime($em, $parentGroupCurrent);
                $this->checkTime($em, $parentGroupNew);
            } else {
                Validator::checkTime($parentGroupCurrent->getLeftEta(), $diffEta);

                $parentGroupCurrent->setLeftEta($parentGroupCurrent->getLeftEta() - $diffEta);

                $this->checkTime($em, $parentGroupCurrent, $diffEta);
            }
            $em->persist($group);
        }
    }
}
