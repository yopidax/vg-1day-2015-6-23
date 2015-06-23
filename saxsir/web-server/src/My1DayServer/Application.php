<?php

namespace My1DayServer;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Response;

use My1DayServer\Exception\ApiExceptionInterface;
use My1DayServer\Exception\InvalidJsonApiException;

class Application extends \Silex\Application
{
    protected $logger;
    protected $tz;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureLogger();
        $this->configureDatabase();
        $this->configureError();
        $this->configureApiSchemaValidator();
        $this->configureDefaultIconImagePath();

        $this->configureRepository();
    }

    public function configureLogger()
    {
        $this->logger = new Logger('api');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../log/api.log', Logger::WARNING));
    }

    public function configureDatabase()
    {
        $this['db_path'] = __DIR__.'/../../db/api.db';
        $this['db'] = \Doctrine\DBAL\DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => $this['db_path'],
        ], new \Doctrine\DBAL\Configuration());
    }

    public function configureError()
    {
        $app = $this;
        $this->error(function (\Exception $e, $code) use ($app) {
            $errors = [];

            if ($e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException) {
                $code = Response::HTTP_NOT_FOUND;
                $errors[] = [
                    'code'    => 'not-found',
                    'message' => '指定されたリソースが見つかりません。',
                ];
            }

            if ($e instanceof ApiExceptionInterface) {
                $code = $e->getHttpStatusCode();
                $errors[] = [
                    'code'    => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ];
            }

            if (empty($errors)) {
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $errors[] = [
                    'code' => 'unexpected',
                    'message' => $app['debug'] ? (string)$e : '予期しないエラーが発生しました。',
                ];
            }

            $level = Logger::WARNING;
            if ($code >= 400 && $code <= 499) {
                $level = Logger::NOTICE;
            } elseif ($code >= 500 && $code <= 599) {
                $level = Logger::ERROR;
            }

            $app->log((string)$e, $level);

            return $app->json($errors, $code);
        });
    }

    public function configureRepository()
    {
        $this['repository.message'] = function($app) { return new Repository\MessageRepository($app['db']); };
        $this['repository.word_count'] = function($app) { return new Repository\WordCountRepository($app['db']); };
    }

    public function configureApiSchemaValidator()
    {
        $this['schema_validator'] = new ApiSchemaValidator();
        $this['schema_validator']->setDefaultSchemaByLocation('file://'.realpath(__DIR__.'/../../doc/schema.json'));
        $this['schema_validator']->setRefResolver(new \JsonSchema\RefResolver());
    }

    public function configureDefaultIconImagePath()
    {
        $this['icon_image_path'] = realpath(__DIR__.'/../../resource/default.jpg');
    }

    public function json($data = [], $status = 200, array $headers = [])
    {
        $result = parent::json($data, $status, array_merge($headers, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
        ]));
        $result->setEncodingOptions(
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT  // Content-Sniffing を悪用した XSS に対する保険的な対策
            | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT // レスポンスの可読性維持のために Unicode 文字は過剰エスケープせず、空白文字で整形する
        );

        return $result;
    }

    public function log($message, $level = Logger::INFO, $context = [])
    {
        return $this->logger->addRecord($level, $message, $context);
    }

    public function getAllMessages()
    {
        $messages = $this['repository.message']->getAllMessages();
        foreach ($messages as $key => $message) {
            $messages[$key] = $this->transformMessageFormatForJsonApi($message);
        }

        return $messages;
    }

    public function getMessage($id)
    {
        if (!$this['repository.message']->isExistingMessage($id)) {
            throw new NotFoundHttpException(sprintf('指定されたメッセージ ID %d は存在しません', $id));
        }

        $message = $this['repository.message']->getMessage($id);

        return $this->transformMessageFormatForJsonApi($message);
    }

    public function deleteMessage($id)
    {
        if (!$this['repository.message']->isExistingMessage($id)) {
            throw new NotFoundHttpException(sprintf('指定されたメッセージ ID %d は存在しません', $id));
        }

        $this['repository.message']->deleteMessage($id);
    }

    public function createMessage($username, $body, $icon)
    {
        $id = $this['repository.message']->createMessage([
            'username' => $username,
            'body' => $body,
            'icon' => $icon,
        ]);

        return $this->getMessage($id);
    }

    protected function getBaseTimezone()     
    {                                        
        if ($this->tz !== null) {
            return $this->tz;
        }

        $this->tz = new \DateTimeZone('UTC');

        return $this->tz;
    }

    protected function transformDateTimeFormat($datetime)
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $datetime, $this->getBaseTimezone())
            ->format('Y-m-d\TH:i:s\Z');
    }

    protected function transformMessageFormatForJsonApi($message)
    {
        $message['id'] = (int)$message['id'];
        $message['created_at'] = $this->transformDateTimeFormat($message['created_at']);
        $message['updated_at'] = $this->transformDateTimeFormat($message['updated_at']);

        return $message;
    }

    public function validateRequestAsJson($request)
    {
        $result = json_decode((string)$request->getContent(), true);
        $error = json_last_error();
        if ($result === null && $error !== JSON_ERROR_NONE) {
            throw new InvalidJsonApiException('指定された JSON のパースに失敗しました。');
        }

        return $result;
    }

    /**
     * 占い結果を返す
     *
     * @return [String] 占い結果
     */
    public function getFortuneTelling() {
      $rand = rand(1, 10);
      switch($rand) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
        case 7:
          return "吉";
          break;
        case 8:
        case 9:
          return "大吉";
          break;
        case 10:
          return "凶";
          break;
        default:
          return "吉";
      }
    }

    /**
     * 受け取った単語をカウントアップする
     *
     * @param word [String] カウントアップする単語
     * @return count [Int] カウントアップした後の値
     */
    public function incrementWordCount($word) {
      // 将来的に'hoge'以外の単語もカウントアップできるようにしたいので
      // 変数で受け取っておく
      // FIXME: あらかじめ決めた単語以外にも対応する場合は、DBにその単語が登録されているかチェックしないとエラーになる
      return $this['repository.word_count']->increment($word);
    }
}
