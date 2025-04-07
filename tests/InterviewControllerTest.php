<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InterviewControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSomething(): void
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('label[for="upload_csv"]', 'Upload CSV file');
        $this->assertSelectorExists('form[name="upload"]');

        $uploadedFile = new UploadedFile(__DIR__.'/test_data.csv', 'test_data.csv');

        $form = $crawler->filter('form[name="upload"]')->form();
        $form['upload[csv]'] = $uploadedFile;
        $this->client->submit($form);
        $this->client->followRedirect();

        $expected ='0.60 3.00 0.00 0.06 1.50 0.00 0.70 0.30 0.30 3.00 0.00 0.00 66.49';
        $this->assertSelectorTextContains('body', $expected);
    }
}
