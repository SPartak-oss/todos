<div class="wrap">
        <h1>Custom API Plugin</h1>
        <form method="post" action="">
            <?php
            if (isset($_POST['sync_data'])) {
				
				if ( class_exists( 'todos' ) ) { // проверка класса
					$todosPropertyCpt = new todosPropertyCpt();// вызов класса
					$todosPropertyCpt->custom_api_plugin_sync_data();
				}

                echo '<div class="updated"><p>Данные синхронизированы</p></div>';
            }
            ?>
            <input type="submit" name="sync_data" class="button button-primary" value="Синхронизировать данные">
        </form>

        <h2>Поиск по заголовку</h2>
        <form method="post" action="">
            <input type="text" name="search_title" placeholder="Введите заголовок">
            <input type="submit" name="search_data" class="button" value="Поиск">
        </form>
        <?php
        if (isset($_POST['search_data']) && !empty($_POST['search_title'])) {
            $title = sanitize_text_field($_POST['search_title']);
            $args = array(
                'post_type'  => 'todo',
                's'          => $title,
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                echo '<h3>Результаты поиска:</h3>';
                echo '<ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li> <a href="' . get_post_permalink() . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
                wp_reset_postdata();
            } else {
                echo '<p>Ничего не найдено</p>';
            }
        }
        ?>
    </div>