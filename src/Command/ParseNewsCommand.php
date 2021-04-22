<?php

namespace App\Command;

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

class ParseNewsCommand extends Command
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

    protected static $defaultName = 'app:parse_news';
    protected static $defaultDescription = 'parse news by url';

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
//        $arg1 = $input->getArgument('arg1');
//
//        if ($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }
//
//        if ($input->getOption('option1')) {
//
//        }

        $response = $this->client->request(Request::METHOD_GET, News::URL_TO_NEWS);

        $log = new Log();
        $log->setResponseCode($response->getStatusCode())
            ->setRequestMethod(Request::METHOD_GET)
            ->setRequestUrl(News::URL_TO_NEWS)
            ->setResponseBody($response->getContent());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $encoder = new XmlEncoder();
        $data = $encoder->decode($response->getContent(), '');

        foreach ($data['channel']['item'] as $item) {
            $news = new News();
            $news
                ->setTitle($item['title'])
                ->setLink($item['link'])
                ->setDescription($item['description'])
                ->setPublishDate(new \DateTime($item['pubDate']))
                ->setAuthor(isset($item['author']) ? $item['author'] : null);
            //            TODO: Изображений может быть много надо отдельно создать изобаражение
            $this->entityManager->persist($news);
        }
        $this->entityManager->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
