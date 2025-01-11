<?php
    /* Template Name: Calendar */
    get_header(); 
    global $wp;

    $current_year = date('Y');
    $current_month = date('m');
    $current_day = date('d');

    // Ayın son gününü hesapla
    $last_day_of_month = date('t', strtotime("{$current_year}-{$current_month}-01"));

    // Bugünün tarihini oluştur
    $current_date = date('Y-m-d'); // Bugünün tam tarihi

    // SQL sorgusunu oluştur
    $query = "
    SELECT * FROM {$wp->posts}
    WHERE YEAR(post_date) = %d
    AND MONTH(post_date) = %d
    AND post_date >= %s 
    AND post_date <= %s
    AND post_status IN ('publish', 'draft')
    ORDER BY post_date ASC
";


    // Sorguyu çalıştır
    $posts = $wp->get_results($wp->prepare($query, $current_year, $current_month, $current_date, "{$current_year}-{$current_month}-{$last_day_of_month}"));


    // wpfe_terms tablosundan verileri al
    $terms_query = "
        SELECT * FROM {$wp->prefix}terms
    ";
    $terms = $wp->get_results($terms_query);

    // Yayınları günlere göre gruplamak için boş bir dizi oluştur
    $grouped_posts = [];

    // Yayınları günlere göre grupla
    foreach ($posts as $post) {
        // Yayın tarihinin sadece gün kısmını al
        $post_date = date('Y-m-d', strtotime($post->post_date));
        
        // Gün bazında gruplama yap
        if (!isset($grouped_posts[$post_date])) {
            $grouped_posts[$post_date] = [];
        }

        $grouped_posts[$post_date][] = $post;
    }

    ?>
    <head>
        <style>
            .date-item {
                background: linear-gradient(to left, rgb(38 39 40 / 15%), rgb(27 28 29 / 31%), rgb(37 39 40 / 21%));
                width: 80px;
                height: 70px;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                color: #fff;
                font-family: Arial, Helvetica, sans-serif;
                font-weight: 700;
                font-size: 15px;
                border-radius: 5px;
                margin: 15px;
                position: relative;
                box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
            }
            .in-page a {
                color: #25045f;
                display: inline-block;
                width: 24%;
            }

            /* Tablet için */
            @media (max-width: 768px) {
                .in-page a {
                    width: 45%;
                }
            }

            /* Mobil için */
            @media (max-width: 480px) {
                .in-page a {
                    width: 95%;
                }
            }

            .c-item-anime {
                width: 100% !important;
                font-size: 12px !important;
                font-weight: 700 !important;
            }
            .c-item {
                background: linear-gradient(to left, rgb(38 39 40 / 15%), rgb(27 28 29 / 31%), rgb(37 39 40 / 21%));
                width: 80px;
                height: 70px;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                color: #fff;
                font-family: Arial, Helvetica, sans-serif;
                font-weight: 700;
                font-size: 15px;
                border-radius: 5px;
                margin: 15px;
                position: relative;
                box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
            }
            .c-released {
                background-color: #f8d405;
                border-radius: 4px;
                border: #f8d405 solid 1px;
                color: #000;
                font-weight: 400;
                width: fit-content;
                height: 20px;
                position: absolute;
                top: 0;
                right: -5px;
                transform: translateY(-23px) translateX(-5px);
                padding-left: 3px;
                padding-right: 3px;
            }
            .c-item-bimg {
                width: 400%;
                height: 50px;
                overflow: hidden;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 8px 8px 0 0;
                margin-left: 10px;
            }
            .c-item-img {
                width: 100px;
                height: 55px;
                border-radius: 5px;
                transition: all .18s ease-in-out;
            }
            .c-item-info {
                width: 1600%;
                padding: 10px;
                text-align: left;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 13px;
            }
            .c-item-bolums{
                display: block;
                width: 100%;
                font-size: 11px;
                font-weight: 400;
            }
            .c-item-episode {
                position: absolute;
                bottom: 15px;
                text-align: left;
                left: 75px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                font-size: 11px;
                font-weight: 400;
                color: #f0e0f0;
                width: 100%;
            }


        </style>
    </head>
    <div class="content">
        <div class="in-page">
        <?php 
        if ($grouped_posts) {
            // Türkçe tarih formatı için yerel ayarları değiştirme
            setlocale(LC_TIME, 'tr_TR.UTF-8'); // Türkçe yerel ayar

            // Gruplandırılmış yayınları günlere göre döngüyle yazdır
            foreach ($grouped_posts as $date => $posts_on_day) {
                        echo '<div class="date-item">
                                ' . ltrim(strftime('%d %B', strtotime($date)), '0') . '
                        </div>';
                
                foreach ($posts_on_day as $post) {
                    // wpfe_terms tablosundaki verilerle post başlığını karşılaştır
                    $post_title = strtolower($post->post_title);
                    
                    foreach ($terms as $term) {
                        // Eğer post başlığı terimi içeriyorsa
                        if (strpos($post_title, strtolower($term->name)) !== false) {
                            // Dizi adı ve bölümü ayırmak için regex kullan
                            if (preg_match('/^(.*?)\s(\d+)\.\sSezon\s(\d+)\.\sBölüm$/i', $post->post_title, $matches)) {
                                $dizi_adi = $matches[1];  // Dizi adı
                                $sezon = $matches[2];      // Sezon numarası
                                $bolum = $matches[3];      // Bölüm numarası
                            } else {
                                $dizi_adi = $post->post_title;
                                $sezon = 'N/A';
                                $bolum = 'N/A';
                            }

                            // wpfe_termmeta tablosundan bolum_afis görselini almak için sorgu
                            $image_query = "
                                SELECT meta_value 
                                FROM {$wp->prefix}termmeta 
                                WHERE term_id = %d 
                                AND meta_key = 'resim_url'
                                LIMIT 1
                            ";
                            $image_url = $wp->get_var($wp->prepare($image_query, $term->term_id));

                            // Eğer görsel URL'si varsa, görseli ekle

                            echo '
                                <a href=" ' . ($post->post_status == 'publish' ? $post->guid : '#wait') . '">
                                    <div class="c-item c-item-anime">
                                        ' . ($post->post_status == 'publish' ? '<p class="c-released">Yayınlandı</p>' : '') . '
                                        
                                        <div class="c-item-bimg">
                                            <img decoding="async" src="' . esc_url($image_url) . '" class="c-item-img" draggable="false">
                                        </div>
                                        <div class="c-item-info">
                                            ' . esc_attr($dizi_adi) . '
                                            <span class="c-item-bolums">' . esc_html($sezon) . '. Sezon ' . esc_html($bolum) . '. Bölüm</span>
                                        </div>
                                    </div>
                                </a>
                            ';



                            break; 
                        }
                    }
                }
            }
        } else {
            echo 'Bu ayda hiç yayın yapılmamış.';
        }
        ?>
        </div>
    </div>

    <?php get_footer(); ?>
