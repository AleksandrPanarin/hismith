<?php

namespace App\Command;

use App\Entity\Image;
use App\Entity\Log;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\HttpClient\HttpClient;

class ParserNewsCommand extends Command
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->client = HttpClient::create();;
        $this->entityManager = $entityManager;
    }

    protected static $defaultName = 'app:parser:news';
    protected static $defaultDescription = 'parser news by url: ' . News::URL_TO_NEWS;

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $response = $this->client->request(Request::METHOD_GET, News::URL_TO_NEWS);
        $log = new Log();
        $log->setResponseCode($response->getStatusCode())
            ->setRequestMethod(Request::METHOD_GET)
            ->setRequestUrl(News::URL_TO_NEWS)
            ->setResponseBody($response->getContent());
        $this->entityManager->persist($log);

        $encoder = new XmlEncoder();
        $data = $encoder->decode($response->getContent(), '');

        if (isset($data['channel']) && isset($data['channel']['item'])) {
            $items = $data['channel']['item'];

            foreach ($items as $item) {
                $news = new News();
                $news
                    ->setTitle($item['title'])
                    ->setLink($item['link'])
                    ->setDescription($item['description'])
                    ->setPublishDate(new \DateTime($item['pubDate']))
                    ->setAuthor(isset($item['author']) ? $item['author'] : null);

                if (isset($item['enclosure']) && $item['enclosure']) {
                    $this->generateImagesForNews($news, $item['enclosure']);
                }
                $this->entityManager->persist($news);
            }
        }
        $this->entityManager->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    /**
     * @param News $news
     * @param array $imagesData
     */
    private function generateImagesForNews(News $news, array $imagesData): void
    {
        if (array_key_exists('@url', $imagesData) && array_key_exists('@type', $imagesData)) {
            if (in_array($imagesData['@type'], Image::ACCEPT_TYPES)) {
                $image = new Image();
                $image->setLink($imagesData['@url'])->setType($imagesData['@type'])->setNews($news);
                $this->entityManager->persist($image);
            }
        } else {
            $this->recursiveImagesDataTraversal($news, $imagesData);
        }

        return;
    }

    /**
     * @param News $news
     * @param array $data
     */
    private function recursiveImagesDataTraversal(News $news, array $data): void
    {
        foreach ($data as $item) {
            if (array_key_exists('@url', $item) && array_key_exists('@type', $item)) {
                if (in_array($item['@type'], Image::ACCEPT_TYPES)) {
                    $image = new Image();
                    $image->setLink($item['@url'])->setType($item['@type'])->setNews($news);
                    $this->entityManager->persist($image);
                }
            } else {
                $this->recursiveImagesDataTraversal($news, $data);
                return;
            }
        }
        return;
    }
}
