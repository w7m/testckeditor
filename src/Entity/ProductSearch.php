<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 22/03/2019
 * Time: 16:57
 */

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class ProductSearch
{

    /**
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "price must be at least {{ limit }} characters long",
     * )
     */
    private $priceMin;

    /**
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "price must be at least {{ limit }} characters long",
     * )
     */
    private $priceMax;

    /**
     * @return mixed
     */
    public function getPriceMin()
    {
        return $this->priceMin;
    }

    /**
     * @param mixed $priceMin
     */
    public function setPriceMin($priceMin): void
    {
        $this->priceMin = $priceMin;
    }

    /**
     * @return mixed
     */
    public function getPriceMax()
    {
        return $this->priceMax;
    }

    /**
     * @param mixed $priceMax
     */
    public function setPriceMax($priceMax): void
    {
        $this->priceMax = $priceMax;
    }


}
