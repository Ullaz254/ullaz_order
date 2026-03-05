<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartProductPrescription extends Model
{
    use HasFactory;

    public function getPrescriptionAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $proxyUrl = \Config::get('app.IMG_URL1');
      $fitUrl = \Config::get('app.FIT_URl');
      // Use HTTP for local development
      if (env('APP_ENV') === 'local') {
        $proxyUrl = str_replace('https://', 'http://', $proxyUrl);
        $fitUrl = str_replace('https://', 'http://', $fitUrl);
      }
      $values['proxy_url'] = $proxyUrl;
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = $fitUrl;
      $values['image_s3_url'] = \Storage::disk('s3')->url($img);
      return $values;
    }
}
