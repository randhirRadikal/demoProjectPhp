<?php
/*
 * Author : Ravi S. Singh
 * Date : 9th March 2016
 * Dependency : AWS PHP API Library
 */
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Aws\S3\S3Client;
class S3Component extends Component {

    public function __construct() {

        //if settings table has the access key and secret set to something create client
        //with those credentials. Else let the client use credentials from instance of server
        if(1){
            $this->s3= S3Client::factory([
                'region'            => 'us-west-2',
                'version'           => '2006-03-01',
                'credentials'=>[
                    'key' => 'AKIAIMNJYAIWQND4YUPQ',
                    'secret' => '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'
                ]
            ]);
        }else{
            $this->s3= S3Client::factory([
                'region'            => 'us-west-2',
                'version'           => '2006-03-01'
            ]);
        }
        $this->bucket='itisforrenttest';
        $this->profilePicPath="profile";

    }

    //function to test if bucket exists in S3 returns false if it dosen't exists
    public function test(){
        return $this->s3->doesBucketExist($this->bucket);
    }



    //profile related functions
    public function saveProfileImage($file){
        if(!$this->test()){
            return FALSE;
        }
        $res=  $this->saveFile($file, $this->profilePicPath);
        if($res['err']){
            return FALSE;
        }else{
            return $res['object_name'];
        }
    }

    public function deleteProfileImage($object_name){
        if(!$this->test()){
            return FALSE;
        }
        $key = $this->profilePicPath."/".$object_name;
        $result = $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);
        return $result;
    }

    public function getProfileImage($object_name){
        $key = $this->profile_pic_path."/".$object_name;
        $url = $this->s3->getObjectUrl($this->bucket,$key);
        return $url;
    }

    public function secGetProfileImage($object_name){
        $key = $this->profile_pic_path . "/" . $object_name;
        $cmd = $this->s3->getCommand('GetObject',[
            'Bucket'=>  $this->bucket,
            'Key' => $key
        ]);
        $request = $this->s3->createPresignedRequest($cmd, '+20 minutes');
        $presignedUrl = (string) $request->getUri();
        return $presignedUrl;
    }


    //internal function to save uploaded file to folder
    private function saveFile($file,$folder,$prefix=NULL){
        //pr($file);exit();
        $name = $file['name'];
        $tmp = $file['tmp_name'];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if(!$prefix){
            $file_name = uniqid(date('HIs')).".".$ext;
        }else{
            $file_name = uniqid(date('HIs')).".".$prefix;
        }
        $key = $folder."/".$file_name;
        $result= $this->s3->putObject([
            'Bucket'=> $this->bucket,
            'Key'=>$key,
            'SourceFile'=>$tmp,
            'ACL'    => 'public-read'//allow public to read the file.
        ]);
        $url= !empty($result['ObjectURL'])?$result['ObjectURL']:'';
        $res=[
            'err'=>1,
            'msg'=>'failed',
            'object_name'=>$file_name
        ];
        if(!empty($url)){
            $res=[
                'err'=>0,
                'msg'=>'success',
                'object_name'=>$file_name
            ];
        }
        return $res;
    }

    //internal function to delete file from s3
    public function deleteFile($key){
        if(!$this->test()){
            return FALSE;
        }
        $result = $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);
        pr($result);
    }

    //function to list all files in a bucket
    public function listObjects($folder=NULL){
        if(!$this->test()){
            return FALSE;
        }
        $req=[
            'Bucket' => $this->bucket
        ];
        if($folder){
            $req['Prefix']=$folder;
        }
        $iterator = $this->s3->getIterator('ListObjects',$req);
        foreach ($iterator as $object) {
            echo $object['Key'] . "<br/>";
        }
    }
}
