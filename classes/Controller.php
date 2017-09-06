<?php


class Controller {

    private $db;

    /**
     * Controller constructor.
     * @param DB $db
     */
    public function __construct(DB $db) {
        $this->db = $db;
    }

    private function checkMethod($method){
        $method = strtoupper($method);
        if($_SERVER['REQUEST_METHOD'] !== $method){
            throw new Exception('method must be ' . $method);
        }
    }

    /**
     * Список всех постов
     * @return string
     */
    public function indexAction(): string {
        $this->checkMethod('GET');

        $user = (int)($_REQUEST['user'] ?? 0);
        $page = $_REQUEST['page'] ?? 1;
        $page = (int)$page >= 1 ? (int)$page : 1;
        $posts = $this->db->getAll('select id, post from posts order by id desc limit ' . (($page - 1) * 10) . ', 10');
        if($user){
            /**
             * В случае если указан пользователь, то собираем данные по данному пользователю
             * и по ид постов из выборке в базе likes для определния лайка
             */
            $post_ids = [];
            foreach($posts as $post){
                $post_ids[] = $post['id'];
            }
            $likes = $this->db->getall('select post_id from likes where user_id = ? and post_id in (' . join(',', $post_ids) . ')', [
                $user
            ]);
            $likes_id = [];
            foreach($likes as $like){
                $likes_id[$like['post_id']] = true;
            }
            foreach($posts as &$post){
                $post['like'] = isset($likes_id[$post['id']]);
            }unset($post);
        }

        return json_encode([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Список всех постов
     * @return string
     */
    public function getUsersAction(): string {
        $this->checkMethod('GET');

        $page = $_REQUEST['page'] ?? 1;
        $page = (int)$page >= 1 ? (int)$page : 1;
        $users = $this->db->getAll('select id, username from users limit ' . (($page - 1) * 10) . ', 10');

        return json_encode([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Список пользователей, поставивших лайк
     * @return string
     */
    public function getLikesAction(): string {
        $this->checkMethod('GET');

        $post = (int)($_REQUEST['post'] ?? 0);
        if(!$post){
            return json_encode([
                'success' => false,
                'error' => 'required field noy found'
            ]);
        }
        $data = $this->db->getall('select u.username from likes l left join users u on l.user_id = u.id where l.post_id = ?', [
            $post
        ]);

        return json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Убирание лайка
     * @return string
     */
    public function dislikeAction(){
        $this->checkMethod('POST');

        $user = (int)($_REQUEST['user'] ?? 0);
        $post = (int)($_REQUEST['post'] ?? 0);
        if(!$user || !$post){
            return json_encode([
                'success' => false,
                'error' => 'required field noy found'
            ]);
        }
        $like = $this->db->getone('select count(*) from likes where user_id = ? and post_id = ?', [
            $user,
            $post
        ]);
        if(!$like){
            return json_encode([
                'success' => false,
                'error' => 'Like not found inf db'
            ]);
        }

        /**
         * Так как база данных консистентна, то нет смысла проверять наличие пользователя и поста
         * В случае отсутсвия пользователя или поста, sql выбросит ошибку
         */
        $this->db->query('delete from likes where `user_id` = ? and `post_id` = ?', [
            $user,
            $post
        ]);
        if($this->db->error()){
            return json_encode([
                'success' => false,
                'error' => 'User or post not found'
            ]);
        }
        return json_encode([
            'success' => true
        ]);
    }

    /**
     * Простановка лайка
     * @return string
     */
    public function likeAction(){
        $this->checkMethod('POST');

        $user = (int)($_REQUEST['user'] ?? 0);
        $post = (int)($_REQUEST['post'] ?? 0);
        if(!$user || !$post){
            return json_encode([
                'success' => false,
                'error' => 'required field noy found'
            ]);
        }
        $like = $this->db->getone('select count(*) from likes where user_id = ? and post_id = ?', [
            $user,
            $post
        ]);
        if($like){
            return json_encode([
                'success' => false,
                'error' => 'Like already in db'
            ]);
        }

        /**
         * Так как база данных консистентна, то нет смысла проверять наличие пользователя и поста
         * В случае отсутсвия пользователя или поста, sql выбросит ошибку
         */
        $this->db->query('insert into likes (`user_id`, `post_id`) VALUE (?, ?)', [
            $user,
            $post
        ]);
        if($this->db->error()){
            return json_encode([
                'success' => false,
                'error' => 'User or post not found'
            ]);
        }
        return json_encode([
            'success' => true
        ]);
    }

    /**
     * Добавление поста
     * @return string
     */
    public function addPostAction(): string {
        $this->checkMethod('POST');

        $post = trim($_REQUEST['post'] ?? '');
        if(!$post){
            return json_encode([
                'success' => false,
                'error' => 'post data not found'
            ]);
        }
        if(strlen($post) > 243){
            return json_encode([
                'success' => false,
                'error' => 'max size of post is 243 byte'
            ]);
        }
        $this->db->query('insert into posts (`post`) VALUE (?)', [
            $post
        ]);
        return json_encode([
            'success' => true
        ]);
    }

    /**
     * Добавление пользователя
     * @return string
     */
    public function addUserAction(): string {
        $this->checkMethod('POST');

        $user = trim($_REQUEST['user'] ?? '');
        if(!$user){
            return json_encode([
                'success' => false,
                'error' => 'POST data not found'
            ]);
        }
        $this->db->query('insert into users (`username`) VALUE (?)', [
            $user
        ]);
        return json_encode([
            'success' => true
        ]);
    }


    /**
     * Удаление поста
     * @return string
     */
    public function deletePostAction(): string {
        $this->checkMethod('POST');

        $id = (int)($_REQUEST['id'] ?? 0);
        if (!$id) {
            return json_encode([
                'success' => false,
                'error' => 'not found id'
            ]);
        }
        /**
         * Так как база данных консистентна, то нет смысла проверять наличие поста
         * В случае отсутсвия поста, sql выбросит ошибку
         */
        $this->db->query('delete from posts where id = ?', [
            $id
        ]);
        if($this->db->error()){
            return json_encode([
                'success' => false,
                'error' => 'not found post with id ' . $id
            ]);
        }
        return json_encode([
            'success' => true
        ]);
    }

}