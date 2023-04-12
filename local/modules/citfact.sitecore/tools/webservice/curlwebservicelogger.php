<?

namespace Citfact\SiteCore\Tools\WebService;

use Citfact\SiteCore\Entity\IntegrationRequest\IntegrationRequestRepository;
use Citfact\SiteCore\Tools\WebService\Loggers\BaseLogger;
use Citfact\SiteCore\Tools\WebService\Loggers\NullLogger;

class CurlWebServiceLogger extends CurlWebService
{
    /** @var BaseLogger */
    protected $logger;

    /**
     * CurlWebServiceLogger constructor.
     * @param BaseLogger|null $logger
     */
    public function __construct(?BaseLogger $logger = null)
    {
        parent::__construct();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Метод для выполнения запроса по ссылке.
     *
     * @param string $url Удалённый ресурс.
     * @param array|string $params Тело запроса.
     * @param string $method Название метода (GET, POST, PUT).
     * @param string|null $loggerType
     * @return mixed
     */
    protected function request(string $url, $params = [], string $method = self::METHOD_POST, ?string $loggerType = null)
    {
        $response = parent::request($url, $params, $method);

        $this->writeLog([
            IntegrationRequestRepository::FIELD_TYPE => $loggerType,
            IntegrationRequestRepository::FIELD_REQUEST_URL => $url,
            IntegrationRequestRepository::FIELD_DATA => $params,
            IntegrationRequestRepository::FIELD_RESPONSE_DATA => $response,
            IntegrationRequestRepository::FIELD_RESPONSE_STATUS_CODE => $this->getStatusCode(),
        ]);

        return $response;
    }

    /**
     * @param array $data
     */
    protected function writeLog(array $data): void
    {
        $this->logger->write($data);
    }
}