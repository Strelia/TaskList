<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Helper\ApiException;
use App\Helper\ApiResponse;
use App\Helper\TimeCalc;
use App\Helper\Validator;
use App\Repository\TaskListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tasks/list", name="task_list_")
 */
class TaskListsController extends AbstractController
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index()
    {
        $taskLists = $this->getRepository()->findAllIsset();
        return new ApiResponse('', $taskLists);
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

        $taskList = $this->getRepository()->find((int)$id);
        Validator::checkEmptyObject($taskList, 'taskList');
        if ($taskList->getStatus() === TaskList::STATUS_DELETED) {
            throw new ApiException(ApiResponse::HTTP_GONE, "The task list has been deleted ");
        }

        return new ApiResponse('', $taskList);
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

        $taskList = new TaskList();
        $this->updateTaskList($taskList, $data);
        return new ApiResponse('', $taskList, [], ApiResponse::HTTP_CREATED);
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
        Validator::checkIssetData($data, 'name');
        Validator::checkIssetData($data, 'eta');
        Validator::checkEmptyData($data['name'], 'name');
        Validator::checkEmptyData($data['eta'], 'eta');
        Validator::checkEmptyData($id, 'id');
        Validator::checkNum($id, 'id');

        $taskList = $this->getRepository()->find((int)$id);

        Validator::checkEmptyObject($taskList, 'TaskList');

        $this->updateTaskList($taskList, $data);
        return new ApiResponse('', $taskList, [], ApiResponse::HTTP_NO_CONTENT);
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

        $taskList = $this->getRepository()->find((int)$id);

        Validator::checkEmptyObject($taskList, 'TaskList');

        $taskList->setStatus(TaskList::STATUS_DELETED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($taskList);
        $em->flush();
        return new ApiResponse('', $taskList, [], ApiResponse::HTTP_NO_CONTENT);
    }

    /**
     * @param TaskList $taskList
     * @param array $data
     */
    private function updateTaskList(TaskList $taskList, array $data) {
        $taskList->setName($data['name']);
        $taskList->setEta(TimeCalc::strTimeToSecond($data['eta']));
        $taskList->setLeftEta(TimeCalc::strTimeToSecond($data['eta']));
        $em = $this->getDoctrine()->getManager();
        $em->persist($taskList);
        $em->flush();
    }

    /**
     * @return TaskListRepository
     */
    private function getRepository(): TaskListRepository
    {
        return $this->getDoctrine()->getRepository(TaskList::class);
    }
}
