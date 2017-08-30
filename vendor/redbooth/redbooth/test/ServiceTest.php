<?php
namespace Redbooth\Test;
require_once 'GlobalVar.php';

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedboothService
     */
    protected $object;

    public function setUpMocking()
    {
        $this->object = $this->getMockBuilder('\Redbooth\Service')
                              ->disableOriginalConstructor()
                              ->getMock();
        $mapMethods = array(
            'getActivities',
//          'getComments',
            'getConversations',
            'getMemberships',
            'getNotes',
            'getNotifications',
            'getOrganizations',
            'getPeople',
            'getProjects',
            'getSubtasks',
            'getTaskLists',
            'getTasks',
            'getUsers',
            'getFiles',
            'getMe',
        );
        $map = array();
        foreach ($mapMethods as $method) {
            $map[] = array($method, array(), json_decode(file_get_contents('test/mocks/' . $method . '.mock')));
        }
        $this->object->expects($this->any())
                      ->method('__call')
                      ->will($this->returnValueMap($map));

        $mockMethods = array(
            'getMe',
            'getUser',
            'post',
            'postFile',
            'createConversation',
            'createTask',
            'createSubTask',
            'createTaskList',
            'createNote',
            'createComment',
            'getFile',
            'downloadFile',
            'getLastHeaders',
            'getItemCount'
        );
        $mockAsArray = array(
            'getLastHeaders' => true
        );
        foreach ($mockMethods as $method) {
            $this->object->expects($this->any())
                          ->method($method)
                          ->will($this->returnValue(json_decode(file_get_contents('test/mocks/' . $method . '.mock'), @$mockAsArray[$method])));            
        }
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $integrationTests = GlobalVar::getEnv('integrationTests');
        if (!empty($integrationTests)) {
            $this->object = new \Redbooth\Service(GlobalVar::getEnv('oauthClientId'),
                                                GlobalVar::getEnv('oauthClientSecret'),
                                                GlobalVar::getEnv('oauthAccessToken'),
                                                GlobalVar::getEnv('oauthRefreshToken'),
                                                GlobalVar::getEnv('oauthRedirectUrl'));
        } else {
            $this->setUpMocking();
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Redbooth\Service::post
     * @group posters
     */
    public function testPost()
    {
        $faker = \Faker\Factory::create();
        $data = array('name' => $faker->sentence,
                      'project_id' => GlobalVar::getEnv('projectId'),
                      'task_list_id' => GlobalVar::getEnv('tasklistId'),
                      'comments_attributes' => array(array('body' => $faker->paragraph)),
                      'type' => 'Task',
                      'is_private' => false,
                      'urgent' => false);
        $res = $this->object->post('tasks', $data);
        $this->assertNotNull($res);
        $this->assertInternalType('object', $res);
        $this->assertNotEmpty($res->type);
        $this->assertEquals('Task', $res->type);
    }

    /**
     * @covers \Redbooth\Service::postFile
     * @group filePosters
     */
    public function testPostFile()
    {
        $faker = \Faker\Factory::create();
        $imagePath = $faker->image;
        $data = array('project_id' => GlobalVar::getEnv('projectId'),
                      'backend' => 'redbooth',
                      'is_dir' => 'false');
        $res = $this->object->postFile('files',
                                       $data,
                                       $imagePath);
        $this->assertNotNull($res);
        $this->assertInternalType('object', $res);
        $this->assertNotEmpty($res->id);
        $this->assertNotEmpty($res->name);
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetActivities()
    {
        $res = $this->object->getActivities();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetComments()
    {
        $this->markTestSkipped();
        $res = $this->object->getComments();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetConversations()
    {
        $res = $this->object->getConversations();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetMemberships()
    {
        $res = $this->object->getMemberships();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetNotes()
    {
        $res = $this->object->getNotes();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetNotifications()
    {
        $res = $this->object->getNotifications();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetOrganizations()
    {
        $res = $this->object->getOrganizations();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetPeople()
    {
        $res = $this->object->getPeople();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetProjects()
    {
        $res = $this->object->getProjects();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetSubtasks()
    {
        $res = $this->object->getTasks();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetTaskLists()
    {
        $res = $this->object->getTaskLists();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetUsers()
    {
        $res = $this->object->getUsers();
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
        return $item->id;
    }

    /**
     * @covers \Redbooth\Service::getUser
     * @group getters
     * @depends testGetUsers
     */
    public function testGetUser($id)
    {
        $res = $this->object->getUser($id);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetTasks()
    {
        $res = $this->object->getTasks();
        $this->assertInternalType('array', $res);
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
    }

    /**
     * @covers \Redbooth\Service::__call
     * @group listGetters
     */
    public function testGetFiles()
    {
        $res = $this->object->getFiles();
        $this->assertInternalType('array', $res);
        foreach ($res as $item) {
            $this->assertNotNull($item);
            $this->assertInternalType('object', $item);
            $this->assertNotEmpty($item->id);
        }
        // make the last file id available to dependents
        return $item;
    }

    /**
     * @covers \Redbooth\Service::getFile
     * @group fileGetters
     * @depends testGetFiles
     */
    public function testGetFile($file)
    {
        $res = $this->object->getFile($file->id);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
        $this->assertNotEmpty($res->name);
    }

    /**
     * @covers \Redbooth\Service::downloadFile
     * @group fileDownloaders
     * @depends testGetFiles
     */
    public function testDownloadFile($file)
    {
        $res = $this->object->downloadFile($file->id, $file->name);
        $this->assertInternalType('string', $res);
        $this->assertNotEmpty($res);
    }

    /**
     * @covers \Redbooth\Service::getMe
     * @group getters
     */
    public function testGetMe()
    {
        $res = $this->object->getMe();
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::createConversation
     * @group creators
     */
    public function testCreateConversation()
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createConversation(GlobalVar::getEnv('projectId'),
                                                 $faker->sentence,
                                                 $faker->paragraph,
                                                 false);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::createTask
     * @group creators
     */
    public function testCreateTask()
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createTask(GlobalVar::getEnv('projectId'),
                                         GlobalVar::getEnv('tasklistId'),
                                         $faker->word,
                                         $faker->paragraph);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
        return $res->id;
    }

    /**
     * @covers \Redbooth\Service::createSubTask
     * @depends testCreateTask
     * @group creators
     */
    public function testCreateSubTask($taskId)
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createSubTask($taskId,
                                            $faker->sentence);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::createTaskList
     * @group creators
     */
    public function testCreateTaskList()
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createTaskList(GlobalVar::getEnv('projectId'),
                                             $faker->sentence);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::createNote
     * @group creators
     */
    public function testCreateNote()
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createNote(GlobalVar::getEnv('projectId'),
                                         $faker->word,
                                         $faker->paragraph);
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::createComment
     * @group creators
     * @depends testCreateTask
     */
    public function testCreateComment($taskId)
    {
        $faker = \Faker\Factory::create();
        $res = $this->object->createComment('task',
                                            $taskId,
                                            $faker->paragraph,
                                            $faker->numberBetween(1, 120));
        $this->assertInternalType('object', $res);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        $this->assertNotEmpty($res->id);
    }

    /**
     * @covers \Redbooth\Service::getLastHeaders
     */
    public function testGetLastHeaders()
    {
        $this->object->getTasks();
        $headers = $this->object->getLastHeaders();
        $this->assertInternalType('array', $headers);
        $this->assertNotNull($headers);
        $this->assertNotEmpty($headers);
        $this->assertNotEmpty($headers['status']);
        return $headers;
    }

    /**
     * @covers \Redbooth\Service::getLastHeaders
     * @depends testGetLastHeaders
     */
    public function testGetPaginationTotalObjects($headers)
    {
        $this->assertNotEmpty($headers['paginationtotalobjects']);
        $this->assertTrue(is_numeric($headers['paginationtotalobjects']));
    }

    /**
     * @covers \Redbooth\Service::getItemCount
     * @group testing
     */
    public function testGetItemCount()
    {
        $this->object->getTasks();
        $count = $this->object->getItemCount();
        $this->assertInternalType('int', $count);
    }
}
