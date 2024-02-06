<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ArticleParser;

use GuzzleHttp\Client;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Http\Client as HttpClient;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class ArticleFeedParser extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'article:feedparser';

    /**
     * configure
     *
     */
    protected function configure()
    {
        # - Command configure
        # - see https://symfony.com/doc/current/console.html
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # - Command logic
        # - see https://symfony.com/doc/current/console.html

        $mm = Qore::service(ModelManager::class);
        $sm = Qore::service(SynapseManager::class);

        $client = new Client();

        while (true) {

            $sources = $mm('SM:ArticleParser')->with('type')->all();

            $attributes = [
                'title' => 'title',
                'full-text' => 'content',
            ];
            
            foreach ($sources as $source) {
                $response = $client->get($source->link);
                if ($response->getStatusCode() == 200) {
                    $xml = simplexml_load_string($response->getBody()->getContents());

                    $hashes = [];
                    
                    foreach ($xml->channel->item as $key => $item) {
                        $hashes[] = sha1(strip_tags(htmlspecialchars_decode(trim((string)$item->link))));
                    }

                    $articles = $mm('SM:Article')->where(['@this.hash' => $hashes])->all()->map(fn ($article) => $article->hash)->toList();

                    foreach ($xml->channel->item as $key => $item) {
                        
                        $hash = sha1(strip_tags(htmlspecialchars_decode(trim((string)$item->link))));
                        if (in_array($hash, $articles)) {
                            continue;
                        }

                        $article = $blocks = [];
                        $article['hash'] = $hash;

                        foreach($item as $key => $value) {
                            if ($key == 'title') {
                                $article['title'] = strip_tags(htmlspecialchars_decode(trim((string)$value)));
                            } elseif ($key == 'link') {
                                $article['source'] = strip_tags(htmlspecialchars_decode(trim((string)$value)));
                            } elseif ($key == 'full-text') {
                                $blocks[] = [
                                    'id' => uniqid(),
                                    'type' => 'paragraph',
                                    'data' => [
                                        'text' => strip_tags(htmlspecialchars_decode(trim((string)$value))),
                                    ],
                                ];
                            } elseif ($key == 'enclosure') {
                                $attributes = [];
                                foreach ($value->attributes() as $key => $v) {
                                    $attributes[$key] = trim((string)$v);
                                }

                                if (isset($attributes['type']) && $attributes['type'] == 'image/jpeg') {
                                    if ($url = $this->uploadImage($attributes['url'])) {
                                        array_unshift($blocks, [
                                            'id' => uniqid(),
                                            'type' => 'image',
                                            'data' => [
                                                'file' => [
                                                    'url' => $url,
                                                ],
                                                'caption' => '',
                                                'withBorder' => false,
                                                'stretched' => true,
                                                'withBackground' => false,
                                            ],
                                        ]);
                                    }
                                }
                            }

                        }

                        $article['content'] = [
                            'time' => time(),
                            'blocks' => $blocks,
                            'version' => '2.82.2',
                        ];

                        $article = $mm('SM:Article', $article);
                        $article->link('type', $source->type());

                        $mm($article)->save();
                    }
                }
            }

            sleep(5 * 60);
        }


        return 0;
    }

    private function uploadImage(string $imageUrl) 
    {
        $mm = Qore::service(ModelManager::class);
        // Отправляем запрос на сервер, чтобы скачать изображение
        $client = new HttpClient();
        $response = $client->send($client->getRequest()->setUri($imageUrl));

        $streamFactory = new StreamFactory();
        $uploadedFileFactory = new UploadedFileFactory();

        // Проверяем, что запрос был успешным
        if ($response->isSuccess()) {
            // Получаем тело ответа (содержимое изображения)
            $body = $response->getBody();

            // Создаем временный файл и записываем в него содержимое изображения
            $tmpFilePath = tempnam(sys_get_temp_dir(), 'downloaded_image');
            file_put_contents($tmpFilePath, $body);

            // Создаем объект файла для загрузки, используя временный файл
            $uploadedFile = $uploadedFileFactory->createUploadedFile(
                $streamFactory->createStreamFromFile($tmpFilePath), // путь к временному файлу
                strlen($body), // размер файла
                UPLOAD_ERR_OK, // код ошибки (если есть)
                basename($imageUrl), // имя файла (получаем из URL)
                mime_content_type($tmpFilePath) // тип содержимого файла (автоматически определяем)
            );

            $image = $mm('SM:ImageStore', ['file' => $uploadedFile]);
            $mm($image)->save();

            return $image->imageUrl;
        }

        return null;
    }

}
