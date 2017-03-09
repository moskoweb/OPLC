<?php
/**
 * Plugin Name: Organizador de Páginas por Linhas Coloridas
 * Plugin URI:  https://github.com/justintadlock/butterbean
 * Description: Metabox para auxiliar na organização das páginas na lista com cores.
 * Version:     1.0.0
 * Author: Alan Mosko
 * Author URI: http://alanmosko.com.br/
 * Text Domain: oplc
*/

function oplc_get_meta( $value ) {
  global $post;

  $field = get_post_meta( $post->ID, $value, true );
  if ( ! empty( $field ) ) {
    return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
  } else {
    return false;
  }
}
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
    wp_enqueue_style( 'wp-color-picker' );
}
function oplc_add_meta_box() {
  add_meta_box(
    'oplc_metabox',
    __( 'Organizador por Linhas Coloridas', 'oplc' ),
    'oplc_html',
    'page',
    'normal',
    'default'
  );
}
add_action( 'add_meta_boxes', 'oplc_add_meta_box' );

function oplc_html( $post) {
  wp_nonce_field( '_oplc_nonce', 'oplc_nonce' ); ?>

  <table>
    <tr>
      <td>
        <label for="oplc_nome"><?php _e( 'Nome:', 'oplc' ); ?></label><br>
        <input type="text" name="oplc_nome" id="oplc_nome" value="<?php echo oplc_get_meta( 'oplc_nome' ); ?>">
      </td>
      <td>&nbsp;</td>
      <td>
        <label for="oplc_cor_back"><?php _e( 'Cor Fundo:', 'oplc' ); ?></label><br>
        <input type="text" name="oplc_cor_back" class="my-color-field" id="oplc_cor_back" value="<?php echo oplc_get_meta( 'oplc_cor_back' ); ?>">
      </td>
      <td>&nbsp;</td>
      <td>
        <label for="oplc_cor_font"><?php _e( 'Cor Fonte:', 'oplc' ); ?></label><br>
        <input type="text" name="oplc_cor_font" class="my-color-field" id="oplc_cor_font" value="<?php echo oplc_get_meta( 'oplc_cor_font' ); ?>">
      </td>
    </tr>
  </table>
  <script>
    jQuery(document).ready(function($){
        $('.my-color-field').wpColorPicker();
    });
  </script>
<?php
}

function oplc_save( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
  if ( ! isset( $_POST['oplc_nonce'] ) || ! wp_verify_nonce( $_POST['oplc_nonce'], '_oplc_nonce' ) ) return;
  if ( ! current_user_can( 'edit_post', $post_id ) ) return;

  if ( isset( $_POST['oplc_nome'] ) )
    update_post_meta( $post_id, 'oplc_nome', esc_attr( $_POST['oplc_nome'] ) );
  if ( isset( $_POST['oplc_cor_back'] ) )
    update_post_meta( $post_id, 'oplc_cor_back', esc_attr( $_POST['oplc_cor_back'] ) );
  if ( isset( $_POST['oplc_cor_font'] ) )
    update_post_meta( $post_id, 'oplc_cor_font', esc_attr( $_POST['oplc_cor_font'] ) );
}
add_action( 'save_post', 'oplc_save' );

// Coluna do Clonador
add_filter('manage_page_posts_columns', 'lea_column_list');
function lea_column_list( $columns ) {
    $columns["color_organize"] = "Organizador";
    return $columns;
}

add_action('manage_page_posts_custom_column', 'lea_columns', 10, 2);
function lea_columns( $colname, $cptid ) {
  if ( $colname == 'color_organize') {
    $id = get_the_ID();
    $oplc_cor_back = get_post_meta($id, 'oplc_cor_back', true);
    $oplc_cor_font = get_post_meta($id, 'oplc_cor_font', true);
    echo get_post_meta($id, 'oplc_nome', true);
    echo "<style>.post-".$id." { background: ".$oplc_cor_back." !important; color: ".$oplc_cor_font." !important; } .post-".$id." a,.post-".$id." td { color: ".$oplc_cor_font." !important; }</style>";
  }
}

function lea_send_email($screen_id) {
    echo
    "<style>
        .column-color_organize { width: 100px !important; text-align: center !important; }
    </style>";
}
add_action('admin_head', 'lea_send_email');