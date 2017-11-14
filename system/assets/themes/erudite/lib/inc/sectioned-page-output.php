<?php

class rhinevestSections{
    
    function default_output($section,$i){
        //ts_data($section);
        global $parallax_ids;
        $eo = ($i+1)%2==0?'even':'odd';
        $title = apply_filters('the_title',$section['content-area-title']);
        $section_name = $section['section-name']!=''?$section['section-name']:$title;
        $slug = sanitize_title_with_dashes(str_replace('/', '-', $section_name));
        $background = '';
        if($section['background-color'] || $section['background-image']){
            if($section['background-color'] && $section['background-image']){
                $background = 'style="background-image: url('.$section['background-image'].');background-color: '.$section['background-color'].';"';
            } elseif($section['background-image']){
                $background = 'style="background-image: url('.$section['background-image'].');"';
            } else{
                $background = 'style="background-color: '.$section['background-color'].';"';
            }
            if($section['background-image'] && $section['background-image-parallax']){
                $parallax_ids[] = $slug;
            }
        }
        $subtitle = $section['content-area-subtitle'] !=''?apply_filters('msdlab_sectioned_page_output_subtitle','<h4 class="section-subtitle">'.$section['content-area-subtitle'].'</h4>'):'';
        $wrapped_title = trim($title) != ''?apply_filters('msdlab_sectioned_page_output_title','<div class="section-title">
            <h3 class="container">
                '.$title.'
            </h3>
            '.$subtitle.'                
        </div>'):'';
        $header = apply_filters('the_content',$section['header-area-content']);
        $content = apply_filters('the_content',$section['content-area-content']);
        $footer = apply_filters('the_content',$section['footer-area-content']);
        $float = $section['feature-image-float']!='none'?' class="align'.$section['feature-image-float'].'"':'';
        $featured_image = $section['content-area-image'] !=''?'<img src="'.$section['content-area-image'].'"'.$float.' />':'';
        $classes = apply_filters('msdlab_sectioned_page_output_classes',array(
            'section',
            'section-'.$slug,
            $section['css-classes'],
            'section-'.$eo,
            'clearfix',
        ));
        //think about filtering the classes here
        $ret = '
        <div id="'.$slug.'" class="'.implode(' ', $classes).'"'.$background.'>
        
                '.$wrapped_title.'
            <div class="section-body">
                <div class="container">
                    '.$featured_image.'
                    '.$header.'
                    '.$content.'
                    '.$footer.'
                </div>
            </div>
        </div>
        ';
        return $ret;
    }


    function column_output($section,$i){
        //ts_data($section);
        global $parallax_ids;
        $eo = ($i+1)%2==0?'even':'odd';
        $breakpoint = apply_filters('msdlab_column_breakpoint', 'sm');
        switch($breakpoint){
            case 'md':
                $next = 'lg';
                break;
            case 'sm':
                $next = 'md';
                break;
            case 'xs':
                $next = 'sm';
                break;
            default:
                $next = 'md';
                break;
        }
        $title = apply_filters('the_title',$section['content-area-title']);
        $section_name = $section['section-name']!=''?$section['section-name']:$title;
        $slug = sanitize_title_with_dashes(str_replace('/', '-', $section_name));
        $background = '';
        if($section['background-color'] || $section['background-image']){
            if($section['background-color'] && $section['background-image']){
                $background = 'style="background-image: url('.$section['background-image'].');background-color: '.$section['background-color'].';"';
            } elseif($section['background-image']){
                $background = 'style="background-image: url('.$section['background-image'].');"';
            } else{
                $background = 'style="background-color: '.$section['background-color'].';"';
            }
            if($section['background-image'] && $section['background-image-parallax']){
                $parallax_ids[] = $slug;
            }
        }
        $subtitle = $section['content-area-subtitle'] !=''?apply_filters('msdlab_sectioned_page_output_subtitle','<h4 class="section-subtitle">'.$section['content-area-subtitle'].'</h4>'):'';
        $wrapped_title = trim($title) != ''?apply_filters('msdlab_sectioned_page_output_title','<div class="section-title">
            <h3 class="container">
                '.$title.'
            </h3>
            '.$subtitle.'                
        </div>'):'';
        $header = $section['header-area-bool']?'<div class="section-header-area">'.apply_filters('the_content',$section['header-area-content']).'</div>':'';
        $content = '<div class="section-content column-1 col-'.$breakpoint.'-12 col-'.$next.'-'.$section['content-area-width'].'">'.apply_filters('the_content',$section['content-area-content']).'</div>';
        $content2 = '<div class="section-content column-2 col-'.$breakpoint.'-12 col-'.$next.'-'.$section['column-2-area-width'].'">'.apply_filters('the_content',$section['column-2-area-content']).'</div>';
        $content3 = '<div class="section-content column-3 col-'.$breakpoint.'-12 col-'.$next.'-'.$section['column-3-area-width'].'">'.apply_filters('the_content',$section['column-3-area-content']).'</div>';
        $content4 = '<div class="section-content column-4 col-'.$breakpoint.'-12 col-'.$next.'-'.$section['column-4-area-width'].'">'.apply_filters('the_content',$section['column-4-area-content']).'</div>';
        $footer = $section['footer-area-bool']?'<div class="section-footer-area">'.apply_filters('the_content',$section['footer-area-content']).'</div>':'';
        $float = $section['feature-image-float']!='none'?' class="align'.$section['feature-image-float'].'"':'';
        $featured_image = $section['content-area-image'] !=''?'<img src="'.$section['content-area-image'].'"'.$float.' />':'';
        $classes = apply_filters('msdlab_sectioned_page_output_classes',array(
            'section',
            'section-'.$slug,
            $section['css-classes'],
            'section-'.$eo,
            'clearfix',
        ));
        switch($section['layout']){
            case 'four-col':
                $central_content = $content4;
            case 'three-col':
                $central_content = $content3.$central_content;
            case 'two-col':
                $central_content = $content2.$central_content;
            default:
                $central_content = $content.$central_content;
                break;
        }

        $central_content = '<div class="row">'.$central_content.'</div>';
        //think about filtering the classes here
        $ret = '
        <div id="'.$slug.'" class="'.implode(' ', $classes).'"'.$background.'>
        
                '.$wrapped_title.'
            <div class="section-body">
                <div class="container">
                    '.$featured_image.'
                    '.$header.'
                    '.$central_content.'
                    '.$footer.'
                </div>
            </div>
        </div>
        ';
        return $ret;
    }

    function sectioned_page_output(){
        wp_enqueue_script('sticky',WP_PLUGIN_URL.'/'.plugin_dir_path('msd-specialty-pages/msd-specialty-pages.php'). '/lib/js/jquery.sticky.js',array('jquery'),FALSE,TRUE);
        global $post,$subtitle_metabox,$sectioned_page_metabox,$nav_ids;
        $i = 0;
        $meta = $sectioned_page_metabox->the_meta();
        if(is_object($sectioned_page_metabox)){
            while($sectioned_page_metabox->have_fields('sections')){
                $hide = $sectioned_page_metabox->get_the_value('section-hidden-bool');
                if($hide){$i++;continue;}
                $layout = $sectioned_page_metabox->get_the_value('layout');
                switch($layout){
                    case "four-col":
                        $sections[] = self::column_output($meta['sections'][$i],$i);
                        break;
                    case "three-col":
                        $sections[] = self::column_output($meta['sections'][$i],$i);
                        break;
                    case "two-col":
                        $sections[] = self::column_output($meta['sections'][$i],$i);
                        break;
                    default:
                        $sections[] = self::default_output($meta['sections'][$i],$i);
                        break;
                }
                $i++;
            }//close while
            print '<div class="sectioned-page-wrapper">';
            print implode("\n",$sections);
            print '</div>';
        }//clsoe if
    }
}