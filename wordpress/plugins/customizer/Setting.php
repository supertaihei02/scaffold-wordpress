<?php
/* *******************************
 *           基本設定
 * *******************************/
function banner_menu() {
    add_options_page(
        '基幹設定', '基幹設定', 'manage_options',
        SI_SETTING_BACKBONE, SI_SETTING_BACKBONE);
    add_action( 'admin_init', 'register_setting_keys' );
}
add_action('admin_menu', 'banner_menu');


function register_setting_keys() {
    register_setting( SI_SETTING_BACKBONE, 'twig_debug_mode' );
}

function backbone() {
    ?>
    <div class="wrap">
        <h2>基幹設定</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields(SI_SETTING_BACKBONE);
            do_settings_sections(SI_SETTING_BACKBONE);
            ?>
            <h3>テンプレートエンジン</h3>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="banner_url">デバッグモード</label></th>
                    <td>
                        <?php
                        $twig_debug_mode = get_option('twig_debug_mode');
                        $twig_debug_mode_on = $twig_debug_mode === 'on' ? 'checked' : '';
                        $twig_debug_mode_off = $twig_debug_mode_on === 'checked' ? '' : 'checked';
                        ?>
                        <input type="radio" class="regular-text" name="twig_debug_mode" value="on" <?php echo $twig_debug_mode_on; ?>>ON
                        <input type="radio" class="regular-text" name="twig_debug_mode" value="off" <?php echo $twig_debug_mode_off; ?>>OFF
                    </td>
                </tr>
                </tbody>
            </table>
            <hr>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}