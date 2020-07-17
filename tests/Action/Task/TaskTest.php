<?php

namespace App\Tests\Action\Task;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Tests\Action\AbstractActionTest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskTest
 */
class TaskTest extends AbstractActionTest
{
    protected $url = '/api/task';
    /**
     * @dataProvider createDataProvider
     *
     * @param array $data
     * @param int   $status
     * @param array $contains
     */
    public function testCreate($data, $status, $contains)
    {
        $this->loadFixtures([AppFixtures::class]);
        $this->headers['PHP_AUTH_USER'] = 'test';
        $this->headers['PHP_AUTH_PW'] = 'test';
        unset($this->headers['HTTP_Authorization']);

        $this->createItem($data, $this->url, $status, $contains);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [
                [
                    'description' => 'description',
                ],
                Response::HTTP_OK,
                'description',
            ],
        ];
    }

    /**
     * Return name of model.
     *
     * @return string
     */
    protected function getEntityName()
    {
        return Task::class;
    }
}
