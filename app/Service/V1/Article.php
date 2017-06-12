<?php
namespace App\Service\V1;

use App\Service\Service;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;

class Article extends Service
{
    function __construct(ContainerInterface $c, $uuid)
    {
        parent::__construct($c, $uuid);
    }

    public function rules()
    {
        return [
            'title' => [
                'label'    => '标题',
                'required' => true,
            ],
            'author_id' => [
                'label'    => '作者ID',
                'valids'   => [
                    v::intVal(),
                ],
                'required' => true,
            ],
            'content' => [
                'label'    => '内容',
                'required' => true,
            ],
        ];
    }

    // 处理文章列表请求
    public function handleList(&$code, &$msg, &$resp)
    {
        $dbData = $this->container->ArticleDao->getAll();

        $resp = $dbData;

        return;
    }

    // 处理文章详情请求
    public function handleDetail($id, &$code, &$msg, &$resp)
    {
        $cacheData = $this->container->ArticleCache->getArticleById($id);

        if (!empty($cacheData)) {
            $resp = $cacheData;
            return;
        }

        $dbData = $this->container->ArticleDao->getById($id);

        if (empty($dbData)) {
            $resp = null;
            return;
        }

        $this->container->ArticleCache->setArticleById($id, $dbData);

        $resp = $dbData;

        return;
    }

    // 处理文章添加请求
    public function handleAdd($postData, &$code, &$msg, &$resp)
    {
        $id = $this->container->ArticleDao->addNewRecord($postData);

        if (!$id) {
            $code = -1;
            $msg = 'failed';

            return;
        }

        $resp = $id;

        return;
    }

    // 处理文章编辑请求
    public function handleUpdate($id, $putData, &$code, &$msg)
    {
        // 删除文章缓存
        $this->container->ArticleCache->delArticleById($id);

        $result = $this->container->ArticleDao->updateById($id, $putData);

        if ($result === false) {
            $code = -1;
            $msg = 'failed';
        }

        return;
    }

    // 处理文章删除请求
    public function handleDelete($id, &$code, &$msg)
    {
        // 删除文章缓存
        $this->container->ArticleCache->delArticleById($id);

        $result = $this->container->ArticleDao->deleteById($id);

        if ($result === false) {
            $code = -1;
            $msg = 'failed';
        }

        return;
    }
}
?>