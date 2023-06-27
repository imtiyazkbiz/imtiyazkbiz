<?php

namespace App\Http;

use App\Exceptions\SafeException;
use App\Models\Model;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Response extends \Illuminate\Http\Response {
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $content = [];

    /**
     * Response constructor.
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
        parent::__construct('');
        $this->header('Content-Type', 'application/json');
        $this->addCORSHeaders($request);
    }

    protected function addCORSHeaders(Request $request) {
        $origin = $request->header('Origin');
        $allowedHeaders = $request->header('Access-Control-Request-Headers', 'Content-Type');
        if ($this->isValidOrigin($origin)) {
            $this->header('Access-Control-Allow-Origin', $origin);
            $this->header('Vary', 'Origin');
            $this->header('Access-Control-Allow-Headers', $allowedHeaders);
            $this->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $this->header('Access-Control-Max-Age', 60 * 60 * 24 * 7);
        }
    }

    protected function isValidOrigin($origin) {
        $valid = config('cors.origins', []);

        return in_array($origin, $valid);
    }

    /**
     * @return Response
     */
    public static function build() {
        return app()->make(Response::class)->setOutput([]);
    }

    /**
     * @inheritdoc
     */
    public function sendContent() {
        echo $this->getContent();

        return $this;
    }

    /**
     * @param bool $returnArray
     *
     * @return string
     * @throws Exception
     */
    public function getContent($returnArray = FALSE) {
        $array = [
            'status' => [
                'code' => $this->getStatusCode(),
                'message' => self::$statusTexts[$this->getStatusCode()],
            ],
        ];
        $data = array_merge($array, $this->content);

        if ($returnArray) {
            return $data;
        }
        $json = json_encode($data);

        if (JSON_ERROR_NONE != json_last_error()) {
            throw new Exception('Could not convert transformed data to JSON representation');
        }

        return $json;
    }

    /**
     * @param Model|Builder|array $data
     *
     * @return $this
     * @throws Exception
     */
    public function setData($data) {
        $perPage = (int)$this->request->query('perPage', 15);
        if ($data instanceof Model) {
            $transformer = $data->buildTransformer();
            $resourceKey = $data->getTable();
            $resource = new Item($data, $transformer, $resourceKey);
        } else if ($data instanceof Builder) {
            $model = $data->getModel();
            $transformer = $model->buildTransformer();
            $resourceKey = $model->getTable();

            $paginator = $data->paginate($perPage);
            $collection = $paginator->items();
            $paginator = new IlluminatePaginatorAdapter($paginator);

            $resource = new Collection($collection, $transformer, $resourceKey);
            $resource->setPaginator($paginator);
        } else if ($data instanceof \Illuminate\Database\Eloquent\Collection) {
            if ($data->count() == 0) {
                $paginator = new IlluminatePaginatorAdapter(new LengthAwarePaginator([], 0, $perPage));
                $resource = new Collection([], function() {
                }, NULL);
                $resource->setPaginator($paginator);
            } else {
                $model = $data->first();
                $transformer = $model->buildTransformer();
                $resourceKey = $model->getTable();
                $page = $this->request->get('page', 1);
                $items = $data->forPage($page, $perPage);
                $paginator = new LengthAwarePaginator($items, $data->count(), $perPage, $page);
                $paginator = new IlluminatePaginatorAdapter($paginator);
                $resource = new Collection($items, $transformer, $resourceKey);
                $resource->setPaginator($paginator);
            }
        } else if (is_array($data)) {
            return $this->setOutput($data);
        } else {
            throw new SafeException(500, ['message' => 'Could not transform']);
        }

        $manager = new Manager();
        $manager->setSerializer(new Serializer());
        $manager->parseIncludes($this->request->query('include', []));

        $output = $manager->createData($resource)->toArray();

        return $this->setOutput($output);
    }

    /**
     * @param array $serialized
     * @param array $error
     *
     * @return Response
     * @throws Exception
     */
    public function setOutput($serialized = NULL, $error = NULL) {
        $array = [];

        if (!is_null($serialized)) {
            $array['data'] = $serialized;
        }

        if (!is_null($error)) {
            $array['error'] = (array)$error;
        }

        $this->content = $array;

        return $this;
    }

    public function setError($error) {
        return $this->setOutput(NULL, $error);
    }
}
