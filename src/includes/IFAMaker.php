<?php

require_once('includes/Countries.php');

class IFAMaker
{
    const FILE = 'IFABejelento2020RCS.jpg';
    const FONT = '/var/www/regisztracio.rcs.hu/ArialCE.ttf';
    const FONT_SIZE = 45;

    /**
     * @var Contact
     */
    private $contact;

    private $image;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function make()
    {
        $this->image = imagecreatefromjpeg(self::FILE);
        $black = imagecolorallocate($this->image, 0, 0, 0);

        $country_name = Countries::EU[$this->contact->getNationality()]['name'];

        imagettftext($this->image, self::FONT_SIZE, 0, 830, 1560, $black, self::FONT, $this->contact->getArrivalDate());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 1660, $black, self::FONT, $this->contact->getDepartureDate());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 1760, $black, self::FONT, $this->contact->getLastName() . ' ' . $this->contact->getFirstName());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 1860, $black, self::FONT, $this->contact->getDob());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 1960, $black, self::FONT, $this->contact->getZip());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 2060, $black, self::FONT, $country_name);
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 2200, $black, self::FONT, $this->contact->getIdNumber());
        imagettftext($this->image, self::FONT_SIZE, 0, 830, 2340, $black, self::FONT, $this->contact->getRegNum());

        switch ($this->contact->getExemption()) {
            case 'Kiskoru':
                imagettftext($this->image, 30, 0, 287, 2600, $black, self::FONT, 'X');
                break;
            case 'Soltvadkerti':
                imagettftext($this->image, 30, 0, 287, 2658, $black, self::FONT, 'X');
                break;
            case '70ev':
                imagettftext($this->image, 30, 0, 287, 2720, $black, self::FONT, 'X');
                break;
        }

        if (!empty($this->contact->getExemption())) {
            imagettftext($this->image, self::FONT_SIZE - 5, 0, 1450, 2820, $black, self::FONT, $this->contact->getExemptionProofType());
            imagettftext($this->image, self::FONT_SIZE - 5, 0, 1450, 2920, $black, self::FONT, $this->contact->getExemptionProofNum());

        }



        return $this;
    }

    public function download()
    {
        header("Content-type: image/jpeg");
        header('Content-Disposition: attachment; filename="IFABejelento.jpg"');

        imagejpeg($this->image);

        imagedestroy($this->image);
    }
}
