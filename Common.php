<?php

namespace PetrovEgor;

class Common {

    const ALL_IDS_KEY = 'youtube-checker-meta-key';
    const UNAVAILABLE_IDS_KEY = 'unavailable-youtube-checker-meta-key';
    const TIME_KEY = 'youtube-checker-meta-time';

    /**
     * @param \WP_Post $post
     * @return \DateTime|null
     */
    public static function getPostLastCheckTime($post)
    {
        $postLastCheckTime = get_post_meta($post->ID, self::TIME_KEY);
        if (sizeof($postLastCheckTime) > 0) {
            Logger::info('post  ' . $post->ID . ', postLastCheckTime: ' . $postLastCheckTime[0]);
            return new \DateTime($postLastCheckTime[0]);
        } else {
            Logger::info('post  ' . $post->ID . ', no postLastCheckTime');
        }
        return null;
    }

    /**
     * @param \WP_Post $post
     * @return \DateTime
     */
    public static function getPostLastUpdateTime($post)
    {
        Logger::info('post  ' . $post->ID . ', postLastUpdateTime: ' . $post->post_modified_gmt);
        return new \DateTime($post->post_modified_gmt);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public static function getYoutubeIdsByPost($post)
    {
        return get_post_meta($post->ID, self::ALL_IDS_KEY);
    }

    /**
     * @param string $id
     * @return bool
     */
    public static function isVideoAvailable($id)
    {
        $url = 'https://www.googleapis.com/youtube/v3/videos?';
        $params = [
            'id' => $id,
            'key' => 'AIzaSyCkml-hsS5ElMNNiW0R-Vf7hIrWyLwq-wU',
            'part' => 'status',
        ];
        $url .= http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $out = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if (strlen($error) > 0) {
            Logger::info('curl error:  ' . $error);
        }
        $result = json_decode($out, true);
        if (isset($result['pageInfo']) && isset($result['pageInfo']['totalResults'])) {
            if ($result['pageInfo']['totalResults'] > 0) {
                Logger::info('ok video: ' . $id);
                return true;
            } else {
                Logger::info('problem video: ' . $id);
                return false;
            }
        }
    }

    /**
     * @param \WP_Post $post
     */
    public static function resetUnavailableVideoListForPost($post)
    {
        delete_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY);
    }

    /**
     * @param \WP_Post $post
     * @param string $videoId
     */
    public static function reportVideoUnavailable($post, $videoId)
    {
        add_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY, $videoId);
    }

    public static function getUnavailableVideoList()
    {
        get_metadata('', '');
    }
}
