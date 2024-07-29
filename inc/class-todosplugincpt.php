<?php
if ( !class_exists( 'todosPropertyCpt' ) ) {
    class todosPropertyCpt{

        public function  register() {
            add_action( 'init', array( $this, 'custom_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box_property' ) ); // хук мета бокса
            add_action( 'save_post',      array( $this, 'save_metaboxes'),10,2 ); // хук сохранения метабокса

            add_action('manage_todo_posts_columns', [$this,'custom_colums_for_apartment']); // Фильтрует столбцы, отображаемые в таблице списка сообщений.
            add_action('manage_todo_posts_custom_column', [$this,'custom_property_columns_data'],10,2); //Срабатывает в каждом настраиваемом столбце таблицы списка сообщений.
            add_filter('manage_edit-todo_sortable_columns', [$this,'custom_property_columns_sort']); // Фильтрует сортируемые столбцы таблицы списка для определенного экрана.
            add_action('pre_get_posts',[$this,'custom_property_order']);
        }

        public function  add_meta_box_property() {
            add_meta_box( // Добавляем метабокс
				'todoproperty_settings', // ид
				__( 'Property Setings', 'todos' ), //имя
				array( $this, 'metabox_property_html' ), // вызов функци для фронта
				'todo', // пост тайп
				'normal',// Контексты экрана постредактирования включают 'normal', 'side' и 'advanced'. Контексты экрана комментариев включают 'normal' и 'side'.
				'default'// Приоритет в контексте, в котором должно отображаться поле.
			);
        }
		
		// Функция для синхронизации данных с внешним API
		public function custom_api_plugin_sync_data() {
			$response = wp_remote_get('https://jsonplaceholder.typicode.com/todos');

			if (is_wp_error($response)) {
				error_log('Ошибка при получении данных из API: ' . $response->get_error_message());
				return;
			}

			$body = wp_remote_retrieve_body($response);
			$todos = json_decode($body);

			if (empty($todos)) {
				error_log('Пустой ответ от API');
				return;
			}

			foreach ($todos as $todo) {
				$post_args = array(
					'post_type'   => 'todo',
					'post_title'  => sanitize_text_field($todo->title),
					'post_status' => 'publish',
					'meta_input'  => array(
						'user_id'   => $todo->userId,
						'completed' => (int) $todo->completed,
					),
				);

				$existing_post_id = post_exists($todo->title);
				if ($existing_post_id) {
					$post_args['ID'] = $existing_post_id;
					wp_update_post($post_args);
				} else {
					wp_insert_post($post_args);
				}
			}

			error_log('Синхронизация данных завершена успешно');
		}

        public function metabox_property_html( $post ) {
            wp_nonce_field( 'todo_inner_custom_box', 'todo_inner_custom_box_nonce' ); // nonce для проверки

            $completed = get_post_meta( $post->ID, 'completed', true );// мета поста
            $user_id = get_post_meta( $post->ID, 'user_id', true );// мета поста

            echo '
            <p>
                <label for="shplugin_price">'.esc_html__('User id','todos').' </label>
                <input type="number" id="todo_user_id" name="todo_user_id" value="'.esc_attr( $user_id ).'"  />
            </p>
            <p>
                <label for="shplugin_period"> '.esc_html__('Completed ','todos').'</label>
                <input type="text" id="todo_completed" name="todo_completed" value="'.esc_attr( $completed ).'"  />
            </p>
            ';
        }

        public function save_metaboxes( $post_id, $post ) {

            if ( ! isset( $_POST['todo_inner_custom_box_nonce'] ) ) {
                return $post_id;
            }
    
            $nonce = $_POST['todo_inner_custom_box_nonce'];
    
            if ( ! wp_verify_nonce( $nonce, 'todo_inner_custom_box' ) ) {  // nonce проверка 
                return $post_id;
            }
    
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { //отключить автосохранение
                return $post_id; 
            }

            if ( 'todo' != $post->post_type ) { // проверяем тип поста
                return $post_id;
            }
        
            if ( ! current_user_can( 'edit_post', $post_id ) ) { //проверка разрешения редактирования поста
                return $post_id;
            }

         
            if(is_null( $_POST['todo_completed'])){
                delete_post_meta( $post_id, 'completed' );
            }else{
                update_post_meta( $post_id, 'completed',  sanitize_text_field( absint($_POST['todo_completed']) ) );
            }
            if(is_null( $_POST['todo_user_id'])){
                delete_post_meta( $post_id, 'user_id' );
            }else{
                update_post_meta( $post_id, 'user_id',  sanitize_text_field( $_POST['todo_user_id'] ) );
            }
          
        }
    
        //Регистр пост тайпа
        public function custom_post_type () { 
               $labels = array(
				'name'               => 'Todos',
				'singular_name'      => 'Todo',
				'menu_name'          => 'Todos',
				'name_admin_bar'     => 'Todo',
				'add_new'            => 'Добавить новый',
				'add_new_item'       => 'Добавить новый Todo',
				'new_item'           => 'Новый Todo',
				'edit_item'          => 'Редактировать Todo',
				'view_item'          => 'Просмотреть Todo',
				'all_items'          => 'Все Todos',
				'search_items'       => 'Искать Todos',
				'not_found'          => 'Не найдено',
				'not_found_in_trash' => 'Не найдено в корзине',
			);

			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => 'todos'),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array('title', 'editor'),
			);

			register_post_type('todo', $args);
    
  
        }

        public function custom_colums_for_apartment($columns){

            $title = $columns['title'];
            $date = $columns['date'];

            $columns['title'] = $title;
            $columns['date'] = $date;
            $columns['user_id'] = esc_html__('User id','shplugin');
            $columns['completed'] = esc_html__('Completed','shplugin');
            
            return $columns;
    
        }

        public function custom_property_columns_data($column,$post_id){

            $user_id = get_post_meta($post_id,'user_id',true);
            $completedr = get_post_meta($post_id,'completed',true);

            switch($column){
                case 'user_id':
                    echo esc_html($user_id);
                    break;
                case 'completed':
                    echo esc_html($completedr);
                    break;
            }

        }

        public function custom_property_columns_sort($columns){

            $columns['user_id'] = 'user_id';
            $columns['completed'] = 'completed';

            return $columns;

        }

        public function custom_property_order($query){

            if(!is_admin()){
                return;
            }
            $orderby = $query->get('orderby');

            if('user_id' ==  $orderby){
                $query->set('meta_key','user_id');
                $query->set('orderby','meta_value_num');
            }
            if('completed' ==  $orderby){
                $query->set('meta_key','completed');
                $query->set('orderby','meta_value');
            }
        }

    
    }
}


if ( class_exists( 'todosPropertyCpt' ) ) { // проверка класса
    $todosPropertyCpt = new todosPropertyCpt();// вызов класса
    $todosPropertyCpt->register();
}