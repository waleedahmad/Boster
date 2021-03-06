<?php

namespace App\Http\Controllers;

use App\Config;
use App\Post;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncController extends Controller
{
    /**
     * Sync all Blog directory images
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncData(){
        $images = $this->getAllImages('tumblr');

        shuffle($images);

        foreach ($images as $image) {
            if($this->isExtAllowed($image) && $this->getFileSizeMB($image) < 10){
                $this->createPost($image, 'tumblr');
                $this->removeFile($image);
            }else{
                $this->removeFile($image);
            }
        }

        return response()->json(true);
    }

    /**
     * Syncs Social directory images
     * @param $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSocialContent($service){
        $images = $this->getAllImages($service);

        shuffle($images);

        foreach ($images as $image) {
            if($this->isExtAllowed($image) && $this->getFileSizeMB($image) < 10 && $this->getSafeImageName($image)){
                $this->createPost($image, $service);
                $this->removeFile($image);
            }else{
                $this->removeFile($image);
            }
        }

        return response()->json(true);
    }

    /**
     * Validate extension
     * @param $image
     * @return bool
     */
    public function isExtAllowed($image){
        $allowed = ['gif', 'jpeg', 'png', 'bmp', 'jpg'];
        return (in_array($this->getFileExtension($image),$allowed));
    }

    /**
     * Create a new post
     * @param $image
     * @param $type
     * @return bool
     */
    public function createPost($image, $type){

        $caption=   $this->getSafeImageName($image);
        $ext    = 	$this->getFileExtension($image);
        $id     =   $this->getRandomID();
        $uri 	= 	$type.'/'.$id.'.'.$ext;
        $default_tag = $this->getConfig('default_tags');
        $tags   =   ($caption) ? $this->getPostTagsFromCaption($this->getSafeImageName($image)). ','. $default_tag : '';

        $post = new Post([
            'caption'   =>  $caption,
            'file_name' =>  $id.'.'.$ext,
            'uri'       =>  $uri,
            'tags'      =>  $tags,
            'type'      =>  $type
        ]);

        if($post->save()){
            $this->saveFile($image, $id, $ext, $type);
            return true;
        }
        return false;
    }

    /**
     * Save synced file
     * @param $file
     * @param $id
     * @param $ext
     * @param $type
     */
    public function saveFile($file, $id, $ext, $type){
        Storage::disk('public')->put('/'.$type.'/'.$id.'.'.$ext,  File::get($file));
    }

    /**
     * Remove synced file
     * @param $file
     * @return mixed
     */
    public function removeFile($file){
        return File::delete($file);
    }

    /**
     * Get file extension
     * @param $file
     * @return string
     */
    public function getFileExtension($file){
        return strtolower(File::extension($file));
    }

    /**
     * Generate random ID
     * @return string
     */
    public function getRandomID(){
        return str_random(15);
    }

    /**
     * Get all images from sync folder
     * @param $type
     * @return mixed
     */
    public function getAllImages($type){
        switch($type){
            case 'tumblr':
                return File::allFiles($this->getConfig('sync_folder'));
            case 'facebook':
                return File::allFiles($this->getConfig('facebook_sync_folder'));
            case 'pinterest':
                return File::allFiles($this->getConfig('pinterest_sync_folder'));
            default:
                return false;
        }
    }

    /**
     * Safe image file name
     * @param $file
     * @return string
     */
    protected function getSafeImageName($file){
        return trim(preg_replace(['/[^A-Za-z0-9\-,&]/', '/[0-9]+/'], ' ', basename( utf8_encode(File::name($file)))));
    }

    /**
     * Post tags from caption
     * @param $caption
     * @return string
     */
    protected function getPostTagsFromCaption($caption){
        return implode(",", preg_split( "/[&,]/", $caption ));
    }

    /**
     * Calculates file size
     * @param $file
     * @return mixed
     */
    function getFileSizeMB($file){
        return (File::size($file) * .0009765625) * .0009765625;
    }

    public function getConfig($key){
        return Config::where('name','=', $key)->first()->value;
    }
}
