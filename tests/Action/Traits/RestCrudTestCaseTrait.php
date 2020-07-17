<?php

namespace App\Tests\Action\Traits;

use App\Entity\Authentication\AccessToken;
use App\Entity\User;
use App\Tests\Action\ParamWrapper;
use Swift_Message;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Trait RestCrudTestCaseTrait
 */
trait RestCrudTestCaseTrait
{
    protected $url;
    protected $findOneBy = [];

    protected $headers = [
        'Accept' => 'application/json',
        'HTTP_Authorization' => 'Bearer AccessToken_For_Client',
    ];

    /**
     * @param       $url
     * @param int   $statusCode
     * @param array $parameters
     *
     * @return mixed
     */
    public function get($url, $statusCode = Response::HTTP_OK, array $parameters = [])
    {
        $this->getClient()->request(
            Request::METHOD_GET,
            $url,
            $parameters,
            [],
            $this->headers
        );
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param null  $id
     * @param int   $statusCode
     * @param array $parameters
     *
     * @return mixed
     */
    public function getItem($id = null, $statusCode = Response::HTTP_OK, array $parameters= [])
    {
        $this->processParamWrapper($id);

        $this->getClient()->request(
            Request::METHOD_GET,
            $this->getResourceUrl().'/'.($id ?: $this->getExistedObjectId()),
            $parameters,
            [],
            $this->headers
        );
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param null $url
     * @param int $statusCode
     * @return mixed|string
     */
    public function getItemWithCriteria($url = null, $criteria = null, $statusCode = Response::HTTP_OK)
    {
        $this->getClient()->request(
            Request::METHOD_GET,
            $this->getResourceUrl($url).'/'.$this->getExistedObjectId($criteria),
            [],
            [],
            $this->headers
        );
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param null $url
     * @param array $filters
     * @param int $statusCode
     * @param null $headers
     * @param array $contains
     *
     * @return mixed|string
     */
    public function getList(
        $url = null,
        $filters = [],
        $statusCode = Response::HTTP_OK,
        $headers = null,
        $contains = []
    ) {
        $headers = $headers ? $headers : $this->headers;
        $this->processParamWrappers($filters);
        $this->getClient()->request(Request::METHOD_GET, $this->getResourceUrl($url), $filters, [], $headers);
        $this->assertEquals(
            $statusCode,
            $this->getClient()->getResponse()->getStatusCode(),
            $this->getClient()->getResponse()->getContent()
        );

        foreach ((array)$contains as $token) {
            $this->assertContains($token, $this->getClient()->getResponse()->getContent());
        }
        $response = $this->getJsonResponse();

        return $response;
    }

    public static function assertListResult($result, $total, $onPage, $field, $value)
    {
        static::assertArrayHasKey('total', $result);
        static::assertArrayHasKey('entities', $result);
        static::assertEquals($total, $result['total']);
        static::assertCount($onPage, $result['entities']);
        static::assertEquals($value, $result['entities'][0][$field]);
    }

    /**
     * @param $data
     * @param null $url
     * @param int $statusCode
     * @param array $contains
     * @param array $files
     * @return mixed
     */
    public function createItem($data, $url = null, $statusCode = Response::HTTP_CREATED, $contains = [], $files = [])
    {
        $this->processParamWrappers($data);

        $this->getClient()->request(Request::METHOD_POST, $this->getResourceUrl($url), $data, $files, $this->headers);
        $this->assertEquals(
            $statusCode,
            $this->getClient()->getResponse()->getStatusCode(),
            $this->getClient()->getResponse()->getContent()
        );

        foreach ((array)$contains as $token) {
            $this->assertContains($token, $this->getClient()->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    /**
     * @param array      $data
     * @param null|array $id
     * @param string     $url
     * @param int        $statusCode
     * @param array      $contains
     * @param string     $method
     *
     * @return mixed
     */
    public function updateItem(
        $data,
        $id = null,
        $url = null,
        $statusCode = Response::HTTP_OK,
        $contains = [],
        $method = Request::METHOD_PATCH
    ) {
        $this->processParamWrapper($id);
        $id = $id ?: $this->getExistedObjectId();

        $this->getClient()
            ->request($method, $this->getResourceUrl($url).'/'.$id, $data, [], $this->headers);

        $this->assertEquals(
            $statusCode,
            $this->getClient()->getResponse()->getStatusCode(),
            $this->getClient()->getResponse()->getContent()
        );

        foreach ((array)$contains as $token) {
            $this->assertContains($token, $this->getClient()->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    /**
     * @param array $data
     * @param string $url
     * @param string $statusCode
     * @param string $method
     * @param array $contains
     * @return mixed
     */
    public function request(
        array $data,
        string $url,
        string $statusCode,
        string $method,
        array $contains = []
    ) {
        $this->getClient()
            ->request($method, $url, $data, [], $this->headers)
        ;

        $this->assertEquals(
            $statusCode,
            $this->getClient()->getResponse()->getStatusCode(),
            $this->getClient()->getResponse()->getContent()
        );

        foreach ((array)$contains as $token) {
            $this->assertContains($token, $this->getClient()->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    /**
     * @param array    $data
     * @param null|int $id
     * @param string   $url
     * @param int      $statusCode
     * @param array    $contains
     * @param string   $method
     *
     * @return mixed
     */
    public function transitItem(
        $data,
        $id = null,
        $url = null,
        $statusCode = Response::HTTP_OK,
        $contains = [],
        $method = Request::METHOD_PATCH
    ) {
        $this->processParamWrapper($id);
        $id = $id ?: $this->getExistedObjectId();

        $idPart = is_array($id) ? implode('/', $id) : $id;

        $this->getClient()
            ->request($method, $this->getResourceUrl($url)."/$idPart/transit", $data, [], $this->headers);

        $this->assertEquals(
            $statusCode,
            $this->getClient()->getResponse()->getStatusCode(),
            $this->getClient()->getResponse()->getContent()
        );

        foreach ((array)$contains as $token) {
            $this->assertContains($token, $this->getClient()->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($id = null, $statusCode = Response::HTTP_NO_CONTENT, $softDelete = false)
    {
        $paramWrapper = $id;
        $this->processParamWrapper($id);
        $id = $id ?: $this->getExistedObjectId();

        $idPart = is_array($id) ? implode('/', $id) : $id;

        $this->getClient()->request(
            Request::METHOD_DELETE,
            $this->getResourceUrl().'/'.$idPart,
            [],
            [],
            $this->headers
        );
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        $result = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository($this->getEntityName())
            ->findOneBy($paramWrapper instanceof ParamWrapper ? $paramWrapper->getCriteria() : ['id' => $id])
        ;

        if ($statusCode == Response::HTTP_NO_CONTENT && !$softDelete) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
        }

        return $this->getJsonResponse();
    }

    /**
     * Return name of model.
     *
     * @return string
     */
    abstract protected function getEntityName();

    /**
     * {@inheritdoc}
     */
    protected function getJsonResponse()
    {
        $content = $this->getClient()->getResponse()->getContent();

        if ($content) {
            static::assertJson($content);
            $content = json_decode($content, true);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExistedObjectId($criteria = null)
    {
        $object = $this->getObjectOf($this->getEntityName(), $criteria);

        if (!method_exists($object, 'getId')) {
            static::fail('object doesn\'t have getId method');
        };

        return $object->getId();
    }

    /**
     * @param $class
     * @param null $criteria
     * @param bool $fail
     * @return mixed
     */
    protected function getObjectOf($class, $criteria = null, $fail = true)
    {
        $criteria = $criteria ?: $this->findOneBy;

        $object = $this->getClient()->getContainer()->get('doctrine.orm.default_entity_manager')
            ->getRepository($class)
            ->findOneBy($criteria)
        ;

        if (!$object && $fail) {
            static::fail('test object ('.$class.') not found: ' . print_r($criteria, true));
        }

        return $object;
    }

    /**
     * @param $class
     * @param null $criteria
     *
     * @return array
     */
    protected function getObjectsOf($class, $criteria = null)
    {
        $criteria = $criteria ?: $this->findOneBy;

        $objects = $this->getClient()->getContainer()->get('doctrine.orm.default_entity_manager')
            ->getRepository($class)
            ->findBy($criteria)
        ;

        return $objects;
    }

    /**
     * @param null|string $url
     *
     * @return string
     */
    protected function getResourceUrl($url = null)
    {
        if (!$url) {
            return $this->url;
        }

        return $url;
    }

    /**
     * @param string $token
     * @return $this;
     */
    protected function setToken($token)
    {
        $this->headers['HTTP_Authorization'] = $token;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    protected function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    protected function processParamWrappers(array &$params)
    {
        array_walk_recursive($params, function (&$value) {
            $this->processParamWrapper($value);
        });
    }

    protected function processParamWrapper(&$value)
    {
        if (is_object($value) && $value instanceof ParamWrapper) {
            $criteria = $value->getCriteria();
            $this->processParamWrappers($criteria);

            $entity = $this->getObjectOf($value->getClass(), $criteria);

            $path = $value->getPath();
            $result = [];

            foreach ((array) $path as $key => $singlePath) {
                $result[$key] = PropertyAccess::createPropertyAccessorBuilder()
                    ->enableExceptionOnInvalidIndex()
                    ->getPropertyAccessor()
                    ->getValue($entity, $singlePath)
                ;
            }

            $value = count($result) > 1 ? $result : $result[0];
        }
    }

    /**
     * @param array $response
     * @param string $orderBy
     * @param \Closure|null $compareFunction
     */
    public function sortingCheck(array $response, string $orderBy, ?\Closure $compareFunction = null)
    {
        $count = count($response['entities']);

        if ($count < 2) {
            throw new \LogicException(
                'To check the sorting you need at least 2 items in the response (count = ' . $count . ')'
            );
        }
        $parts = explode('|', $orderBy);
        $field = $parts[0];
        $direct = $parts[1] ?? 'asc';
        $directNumber = $direct === 'DESC' || $direct === 'desc' ? 1 : -1;

        for ($i = 1; $i < $count; ++$i) {
            if ($compareFunction) {
                $compare = $compareFunction($response['entities'][$i - 1], $response['entities'][$i]);
            } else {
                $compare = self::pgCompare($response['entities'][$i - 1][$field], $response['entities'][$i][$field]);
            }

            if ($directNumber !== $compare && $compare !== 0) {
                self::fail(
                    'Value of "'.$field.'" property of the '.($i - 1).' and '
                    .$i.' element does not correspond to the order'
                );
            }
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function pgPrepareCompareValue($value)
    {
        if (is_string($value)) {
            $value = strtr($value, [' ' => '', '-' => '']);
        }

        return $value;
    }

    /**
     * @param $left
     * @param $right
     *
     * @return int
     */
    public static function pgCompare($left, $right)
    {
        $left = self::pgPrepareCompareValue($left);
        $right = self::pgPrepareCompareValue($right);

        if ($left !== null && $right === null) {
            return -1;
        }

        if ($left === null && $right !== null) {
            return 1;
        }

        return $left <=> $right;
    }

    /**
     * @param $token - AccessToken_For_Admin | Bearer AccessToken_For_Admin
     *
     * @return User|null
     */
    public function getUserByToken($token)
    {
        if (strpos($token, 'Bearer') > -1) {
            $token = explode(' ', $token);
            $token = $token[1] ?? $token[0];
        }

        /** @var AccessToken $token */
        $token = $this->getObjectOf(AccessToken::class, ['token' => $token]);

        return $token ? $token->getUser() : null;
    }

    /**
     * @param $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->getObjectOf(User::class, ['email' => $email]);
    }

    public static function assertEqualsArrays($expected, $actual, $message = 'Array not equals')
    {
        if (count(array_diff($expected, $actual)) !== 0) {
            echo $message, "\n";
            echo "Expected: \n";
            print_r($expected);
            echo "Actual: \n";
            print_r($actual);
            self::fail();
        }

        self::assertTrue(true);
    }

    /**
     * @param $startId
     * @param null $class
     *
     * @return mixed
     */
    protected function getNonExistentId($class, $startId = 100)
    {
        $repository =  $this
            ->getClient()
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($class)
        ;

        while ($repository->find($startId)) {
            $startId += 100;
        }

        return $startId;
    }

    /**
     * @return int
     */
    public function getMessageCount()
    {
        $emailCollector = $this->getClient()->getProfile()->getCollector('swiftmailer');

        return $emailCollector->getMessageCount();
    }

    /**
     * @param int $i
     *
     * @return Swift_Message | null
     */
    public function getMessage($i = 0)
    {
        $messages = $this->getClient()->getProfile()->getCollector('swiftmailer')->getMessages();

        return isset($messages[$i]) ? $messages[$i] : null;
    }

    /**
     * @return MessageDataCollector
     */
    public function getEmailCollector()
    {
        return $this->getClient()->getProfile()->getCollector('swiftmailer');
    }

    public function enableProfiler()
    {
        $this->getClient()->enableProfiler();
    }
}
