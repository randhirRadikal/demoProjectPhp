<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity
 *
 * @property int $id
 * @property string $access_token
 * @property string $first_name
 * @property string $last_name
 * @property int $phone_number
 * @property string $profile_image
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string $users_role
 * @property string $facebookid
 * @property string $twitterid
 * @property string $googleid
 * @property string $linkedinid
 * @property string $device_type
 * @property string $device_token
 * @property string $user_ip_address
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $created
 * @property string $status
 *
 * @property \App\Model\Entity\Address[] $addresses
 * @property \App\Model\Entity\Cart[] $carts
 * @property \App\Model\Entity\Order[] $orders
 * @property \App\Model\Entity\Package[] $packages
 * @property \App\Model\Entity\Product[] $products
 */
class School extends Entity{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password'
    ];

    protected function _setPassword($password){
        return (new DefaultPasswordHasher)->hash($password);
    }
}
