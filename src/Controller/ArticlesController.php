<?php
// src/Controller/ArticlesController.php
namespace App\Controller;

use App\Controller\AppController;

class ArticlesController extends AppController
{
    // initialize(): 该方法用于初始化控制器。在这里，它调用了父类的 initialize() 方法，然后加载了两个组件，Paginator 和 Flash。Paginator 用于分页，Flash 用于显示提示信息。
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Paginator'); // 这里的 $this 指的是当前控制器对象，通过它调用了 loadComponent 方法加载了 Paginator 组件。
        $this->loadComponent('Flash'); // Include the FlashComponent
    }

    // index(): 用于显示文章列表。通过 $this->Paginator->paginate() 获取分页后的文章数据，并将其传递给视图。
    public function index()
    {
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }

    // view($slug): 根据文章的 slug（唯一标识符）查找并显示单个文章的详细信息。
    public function view($slug)
    {
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->set(compact('article'));
    }

    // add(): 处理添加新文章的逻辑。如果接收到 POST 请求，就尝试保存提交的文章数据。在这里，作者（user_id）被硬编码为 1，这是一个临时的实现。在真正的身份验证系统实现后，这部分会被修改。
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Hardcoding the user_id is temporary, and will be removed later
            // when we build authentication out.
            $article->user_id = 1;

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list')->all();

        // Set tags to the view context
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    //edit($slug): 用于编辑文章。首先，根据 slug 获取要编辑的文章，然后在接收到 POST 或 PUT 请求时，尝试更新文章数据。
    public function edit($slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->firstOrFail();

        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }

        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list')->all();

        // Set tags to the view context
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    //delete($slug): 处理删除文章的逻辑。只允许 POST 或 DELETE 请求，根据 slug 查找要删除的文章，然后尝试删除。
    public function delete($slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
            return $this->redirect(['action' => 'index']);
        }
    }

    //这些方法主要负责处理用户请求，与模型（Model）交互以执行数据库操作，然后将数据传递给视图进行显示。这符合 CakePHP 的 MVC（Model-View-Controller）模式。
}
