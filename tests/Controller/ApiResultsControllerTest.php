<?php

namespace App\Tests\Controller;

use Faker\Factory as FakerFactoryAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * Class ApiUsersControllerTest
 *
 * @package App\Tests\Controller
 *
 * @coversDefaultClass \App\Controller\ApiResultsController
 */
class ApiResultsControllerTest extends BaseTestCase
{
    private const RUTA_API = '/api/v1/results';


    /**
     * Test POST /results 201 Created
     *
     * @return array result data
     * @covers ::postAction()
     */
    public function testPostResultAction201(): array
    {
        $headers = $this->getTokenHeaders();
        $role = self::$faker->word;
        $p_user_data = [
            'email' => self::$faker->email,
            'password' => self::$faker->password,
            'roles' => [ $role ],
        ];
        $p_data = [
            'result' => self::$faker->randomDigit,
            'userEmail' => $p_user_data['email']
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/api/v1/users',
            [],
            [],
            $headers,
            json_encode($p_user_data)
        );
        self::$client->request(
            Request::METHOD_POST,
            self::RUTA_API,
            [],
            [],
            $headers,
            json_encode($p_data)
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $result = json_decode($response->getContent(), true);
        self::assertNotEmpty($result['result']['id']);
        self::assertEquals($p_data['result'], $result['result']['result']);
        return $result['result'];
    }


    /**
     * Test POST /results 400 BadRequest
     *
     * @return void
     * @covers ::postAction()
     */
    public function testPostResultAction400(): void
    {
        $p_data = [
            'result' => self::$faker->randomDigit,
            'userEmail' => self::$faker->email
        ];
        $headers = $this->getTokenHeaders();
        self::$client->request(
            Request::METHOD_POST,
            self::RUTA_API,
            [],
            [],
            $headers,
            json_encode($p_data)
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Test GET /results 200 Ok
     *
     * @return void
     * @covers ::cgetAction()
     * @depends testPostResultAction201
     */
    public function testCGetAction200(): void
    {
        $headers = $this->getTokenHeaders();
        self::$client->request(Request::METHOD_GET, self::RUTA_API, [], [], $headers);
        $response = self::$client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertJson($response->getContent());
        $results = json_decode($response->getContent(), true);
        self::assertArrayHasKey('results', $results);
    }

    /**
     * Test GET /results 200 Ok (XML)
     *
     * @return void
     * @covers ::cgetAction()
     * @covers \App\Controller\Utils::getFormat()
     * @covers \App\Controller\Utils::apiResponse()
     * @depends testPostResultAction201
     */
    public function testCGetAction200XML(): void
    {
        $headers = $this->getTokenHeaders();
        self::$client->request(
            Request::METHOD_GET,
            self::RUTA_API . '.xml',
            [],
            [],
            $headers
        );
        $response = self::$client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertArrayHasKey('content-type', $response->headers->all());
        self::assertEquals('application/xml', $response->headers->get('content-type'));
    }


    /**
     * Test GET /results/{resultId} 200 Ok
     *
     * @param   array $result result returned by testPostResultAction201()
     * @return  void
     * @covers  ::getAction()
     * @depends testPostResultAction201
     */
    public function testGetResultAction200(array $result): void
    {
        $headers = $this->getTokenHeaders();
        self::$client->request(
            Request::METHOD_GET,
            self::RUTA_API . '/' . $result['id'],
            [],
            [],
            $headers
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertJson((string) $response->getContent());
        $result_aux = json_decode((string) $response->getContent(), true);
        self::assertEquals($result['id'], $result_aux['result']['id']);
    }



    /**
     * Test PUT /results/{resultId} 209 Content Returned
     *
     * @param   array $result result returned by testPostResultAction201()
     * @return  array modified result data
     * @covers  ::putAction()
     * @depends testPostResultAction201
     */
    public function testPutResultAction209(array $result): array
    {
        $headers = $this->getTokenHeaders();
        $role = self::$faker->word;
        $p_user_data = [
            'email' => self::$faker->email,
            'password' => self::$faker->password,
            'roles' => [ $role ],
        ];
        $p_data = [
            'result' => self::$faker->randomDigit,
            'userEmail' => $p_user_data['email']
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/api/v1/users',
            [],
            [],
            $headers,
            json_encode($p_user_data)
        );
        self::$client->request(
            Request::METHOD_PUT,
            self::RUTA_API . '/' . $result['id'],
            [],
            [],
            $headers,
            json_encode($p_data)
        );
        $response = self::$client->getResponse();
        self::assertEquals(209, $response->getStatusCode());
        self::assertJson((string) $response->getContent());
        $result_aux = json_decode((string) $response->getContent(), true);
        self::assertEquals($result['id'], $result_aux['result']['id']);
        self::assertEquals($p_data['result'], $result_aux['result']['result']);
        return $result_aux['result'];
    }


    /**
     * Test PUT /results/{resultId} 400 Bad Request
     *
     * @param   array $result result returned by testPostResultAction201()
     * @return  void
     * @covers  ::putAction()
     * @depends testPostResultAction201
     */
    public function testPutResultAction400(array $result): void
    {
        $headers = $this->getTokenHeaders();
        $role = self::$faker->word;
        $p_data = [
            'result' => self::$faker->randomDigit,
            'userEmail' => self::$faker->email
        ];

        self::$client->request(
            Request::METHOD_PUT,
            self::RUTA_API . '/' . $result['id'],
            [],
            [],
            $headers,
            json_encode($p_data)
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Test DELETE /results/{resultId} 204 No Content
     *
     * @param   array $result result returned by testPostResultAction201()
     * @return  int resultId
     * @covers  ::deleteAction()
     * @depends testPostResultAction201
     * @depends testPostResultAction400
     * @depends testGetResultAction200
     * @depends testPutResultAction400
     */
    public function testDeleteResultAction204(array $result): int
    {
        $headers = $this->getTokenHeaders();
        self::$client->request(
            Request::METHOD_DELETE,
            self::RUTA_API . '/' . $result['id'],
            [],
            [],
            $headers
        );
        $response = self::$client->getResponse();

        self::assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
        self::assertEmpty((string) $response->getContent());

        return $result['id'];
    }

}