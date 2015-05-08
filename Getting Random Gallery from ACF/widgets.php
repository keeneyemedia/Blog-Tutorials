<?php
// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

	
add_action( 'widgets_init', function(){
     register_widget( 'Recent_Photos_Widget' );
});	

/**
 * Adds Recent_Photos_Widget widget.
 */
class Recent_Photos_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Recent_Photos_Widget', // Base ID
			__('Recent Photos', 'poway'), // Name
			array('description' => __( 'Displays recent photos from the Galleries', 'poway' ),) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		if ( array_key_exists('before_widget', $args) ) echo $args['before_widget'];
		
		
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			
			// Setting up the loop to get all gallery IDs
			$args = array(
			'post_type' => 'gallery', 
			'posts_per_page' => -1
			);
			$posts_query = new WP_Query($args);
			
			// Setting up the array in which ALL IDs of all galleries will be stored
			$galleries = array();
			
			// Loop through galleries, and add each ID to the $galleries array
			while ($posts_query->have_posts()) {
				$posts_query->the_post();
			    $galleries[] = strval( get_the_ID() );
			}
			wp_reset_postdata();
			
			//Set 'other_page' to random gallery
			$other_page = $galleries[array_rand($galleries)];
			
			
			//Begin loop of ACF gallery based on random gallery
			$images = get_field('gallery_images', $other_page);
			
			if( $images ):
				
				$photocount = 0; ?>
				
			    <ul class="galleries">
			        <?php foreach( $images as $image ): ?>
			            
			            <?php $photocount++;
			            if($photocount <= 6) { //limit to 6 photos ?>
				            <li>
				            	<a href="<?php echo $image['sizes']['large']; ?>" rel="prettyPhoto[<?php echo $post->post_name; ?>]" title="<?php echo $image['caption']; ?>">
				                    <img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" title="<?php echo $image['title']; ?>" class="gallery-thumbnail" />
								</a>
				            </li>
						<?php }
						
			        endforeach; ?>
			    </ul>
			    
			    <a href="<?php echo get_permalink($instance['button_page']); ?>" class="btn btn-default">See More <i class="fa fa-angle-right"></i></a>
			<?php endif;
			
			
		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Recent Photos', 'text_domain' );
		}
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'button_page' ); ?>"><?php _e( 'Page for Button Link:' ); ?></label> 
			<?php
			wp_dropdown_pages( array(
			    'id' => $this->get_field_id('button_page'),
			    'name' => $this->get_field_name('button_page'),
			    'selected' => $instance['button_page'],
			    'show_option_none' => 'Do not display a button',
			) );
			?>		
		</p>
		
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['button_page'] = ( ! empty( $new_instance['button_page'] ) ) ? strip_tags( $new_instance['button_page'] ) : '';
		return $instance;
	}

} // class Recent_Photos_Widget