<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorMedia extends Model
{
	protected $table = 'vendor_media';
  protected $fillable = ['media_type','vendor_id','path'];

    public function getPathAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value) && !is_array($value)){
        $img = $value;
      }
      $img = str_replace(' ', '', $img);
      $ex = checkImageExtension($img);
      $proxyUrl = \Config::get('app.IMG_URL1');
      $fitUrl = \Config::get('app.FIT_URl');
      // Use HTTP for local development
      if (env('APP_ENV') === 'local') {
        $proxyUrl = str_replace('https://', 'http://', $proxyUrl);
        $fitUrl = str_replace('https://', 'http://', $fitUrl);
      }
      $values['proxy_url'] = $proxyUrl;
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
      } else {
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      }
      $values['image_fit'] = $fitUrl;
      $values['original_image'] = \Storage::disk('s3')->url($img);
      return $values;
    }
    
}
