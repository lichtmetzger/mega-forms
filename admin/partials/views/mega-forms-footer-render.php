<?php

/**
 * Render Mega Forms Footer Area
 *
 * @link       https://wpali.com
 * @since      1.0.5
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/admin/partials/views/
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Footer_Render
{

  /**
   * Holds the current page slug
   *
   * @since 1.0.0
   */
  public $current_page = null;

  function __construct()
  {

    $this->render();
  }

  /**
   * MegaForms Footer.
   *
   * @since    1.0.0
   */
  private function render()
  {

    $this->current_page = mf_api()->get_page();

    # Load JS Templates
    $this->insert_js_templates();
  }

  /**
   * Create and return the new form modal HTML element.
   *
   * @since    1.0.5
   */
  public function insert_js_templates()
  {

    if ('mf_form_editor' == $this->current_page || 'mf_forms' == $this->current_page || 'mf_form_entries' == $this->current_page) {
      # Create empty modal
?>
      <script type="text/html" id="tmpl-empty-modal">
        <div id="mgform_modal">
          <div id="mgform_modal_wrapper" class="reveal-modal base-form-modal open">
            <a class="close-reveal-modal close_mgform_modal">×</a>
            <img class="mf_modal_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />
            <div class="mgforms_modal_heading">{{data.heading}}</div>
            <span class="mgforms_modal_desc">{{data.desc}}</span>
            <div id="mgform_modal_inner">
              <div class="mgforms_modal_content">{{{data.content}}}</div>
            </div>
          </div>
          <div class="reveal-modal-bg close_mgform_modal" style="display: block;"></div>
        </div>
      </script>
      <?php
      # Create Form modal.
      $templates = mf_api()->get_ready_form_templates();
      $templates_output = '';
      $templates_output .= '<div class="mf-ready-templates">';
      foreach ($templates as $key => $section) {
        if (!empty($section['templates'])) {
          if (isset($section['label'])) {
            $templates_output .= '<h2 class="mf-ready-templates-label">' . $section['label'] . '</h2>';
          }
          if (isset($section['desc'])) {
            $templates_output .= '<p class="mf-ready-templates-desc">' . $section['desc'] . '</p>';
          }
          $templates_output .= '<div class="mf-ready-templates-list">';
          foreach ($section['templates'] as $template_key => $template_data) {
            $templates_output .= '<a href="#" class="mf-ready-templates-btn" data-hook="create-form" data-template-parent="' . $key . '" data-template-type="' . $template_key . '" data-template-title="' . $template_data['label'] . '">' . $template_data['label'] . '</a>';
          }
          $templates_output .= '</div>';
        }
      }
      $templates_output .= '</div>';
      ?>
      <script type="text/html" id="tmpl-create-form-modal">
        <div id="mgform_modal">
          <div id="mgform_modal_wrapper" class="reveal-modal create-form-modal open">
            <a class="close-reveal-modal close_mgform_modal">×</a>
            <div id="mgform_modal_inner">
              <form method="post">
                <span class="mf_clearfix"></span>
                <div class="mfield mf_left">
                  <h3>{{data.title}}</h3>
                  <input id="new_mgform_title" data-hook="modal-title-input" type="text" name="name" value="" placeholder="{{data.label}}">
                </div>
                <div class="mfield mf_right">
                  <img class="mf_modal_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />
                </div>
                <span class="mf_clearfix"></span>
                <span class="error"></span>
                <?php echo $templates_output; ?>
              </form>
            </div>
            <div style="display:none;" id="response" data-hook="modal-response"><span><img src="{{{data.spinner}}}" /></span></div>
          </div>
          <div class="reveal-modal-bg close_mgform_modal" style="display: block;"></div>
        </div>
      </script>
      <?php
      # Rename Form modal.
      ?>
      <script type="text/html" id="tmpl-rename-form-modal">
        <div id="mgform_modal">
          <div id="mgform_modal_wrapper" class="reveal-modal create-form-modal open">
            <a class="close-reveal-modal close_mgform_modal">×</a>
            <img class="mf_modal_logo" src="<?php echo MEGAFORMS_DIR_URL . 'admin/assets/images/logo-v1.svg' ?>" />
            <div class="mgforms_modal_heading">{{data.heading}}</div>
            <span class="mgforms_modal_desc">{{data.desc}}</span>
            <div id="mgform_modal_inner">
              <form method="post">
                <div class="mfield">
                  <input id="new_mgform_title" data-hook="modal-title-input" type="text" name="name" value="" placeholder="{{data.label}}">
                  <span class="error"></span>
                  <input type="submit" data-hook="modal-title-submit" id="submitButton" name="submitButton" value="{{data.submittext}}" class="button button-large button-primary">
                </div>
              </form>
            </div>
            <div style="display:none;" id="response" data-hook="modal-response"><span><img src="{{{data.spinner}}}" /></span></div>
          </div>
          <div class="reveal-modal-bg close_mgform_modal" style="display: block;"></div>
        </div>
      </script>
    <?php

    }
    if ('mf_form_editor' == $this->current_page) {
      # Field preview on darg
    ?>
      <script type="text/html" id="tmpl-single_field">
        <li data-type="{{data.type}}" data-is-static="1" data-id='{{data.field_id}}' class='single_field loader'>
          <img src='{{data.spinner}}' />
          <div class='field_controls'>
            <div class='field_title mf_left'>
              <span class='field-icon {{data.icon}}'></span>
              {{data.title}}
            </div>
          </div>
          <div class='mf_clearfix'></div>
        </li>
      </script>
      <?php

      # Action preview on darg
      ?>
      <script type="text/html" id="tmpl-single_action">
        <li data-type="{{data.type}}" data-id='{{data.action_id}}' class='single_action loader'>
          <img src='{{data.spinner}}' />
          <div class='action_controls'>
            <div class='action_title mf_left'>
              {{data.title}}
            </div>
          </div>
          <div class='mf_clearfix'></div>
        </li>
      </script>
      <?php
      # Inline Modal
      ?>
      <script type="text/html" id="tmpl-inline-modal">
        <div id="mf_inline_modal" class="disable-sorting">
          <div class="inline_modal_tabs">{{{data.tabs}}}</div>
          <div class="inline_modal_content">{{{data.content}}}</div>
        </div>
      </script>
      <?php
      # Merge tags templates
      $list = mf_merge_tags()->get_tags_list();
      foreach ($list as $group => $tags) {
      ?>
        <script type="text/html" id="tmpl-inline-modal-<?php echo $group; ?>">
          <a href="javascript:void(0)" class="inline-modal-tab-link" data-id="<?php echo $group; ?>"><?php echo $tags['label']; ?></a>
          <div class="inline-modal-content <?php echo $group; ?>-merge-tags" data-id="<?php echo $group; ?>">
            <?php
            foreach ($tags['tags'] as $tag_key => $tag_title) {
            ?>
              <a href="javascript:void(0)" data-tag="{mf:<?php echo esc_attr($group); ?> <?php echo esc_attr($tag_key); ?>}" class="mf_insert_tag">
                <span class="merge-tag-title"><?php echo $tag_title; ?></span>
                <span class="merge-tag-code">{mf:<?php echo $group ?> <?php echo $tag_key ?>}</span>
              </a>
            <?php
            }
            ?>
            {{{data.content}}}
          </div>
        </script>
<?php
      }

      # Containers JS
      foreach (MF_Containers::get_containers() as $container) {
        echo $container->get_editor_inline_JS();
      }
    }

    # Allow adding more templates
    do_action('mf_admin_insert_js_template', $this->current_page);
  }
}
