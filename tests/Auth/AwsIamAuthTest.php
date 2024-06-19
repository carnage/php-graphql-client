<?php

namespace GraphQL\Tests\Auth;

use GraphQL\Auth\AwsIamAuth;
use GraphQL\Exception\AwsRegionNotSetException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AwsIamAuth::class)]
class AwsIamAuthTest extends TestCase
{
    protected AwsIamAuth $auth;

    protected function setUp(): void
    {
        $this->markTestSkipped('Depends on AWS');

        $this->auth = new AwsIamAuth();
    }

    /**
     * @covers \GraphQL\Auth\AwsIamAuth::run
     * @covers \GraphQL\Exception\AwsRegionNotSetException::__construct
     */
    public function testRunMissingRegion(): void
    {
        $this->expectException(AwsRegionNotSetException::class);
        $request = new Request('POST', '');
        $this->auth->run($request, []);
    }

    /**
     * @covers \GraphQL\Auth\AwsIamAuth::run
     * @covers \GraphQL\Auth\AwsIamAuth::getSignature
     * @covers \GraphQL\Auth\AwsIamAuth::getCredentials
     */
    public function testRunSuccess(): void
    {
        $request = $this->auth->run(
            new Request('POST', ''),
            ['aws_region' => 'us-east-1']
        );
        $headers = $request->getHeaders();
        $this->assertArrayHasKey('X-Amz-Date', $headers);
        $this->assertArrayHasKey('X-Amz-Security-Token', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
    }
}
