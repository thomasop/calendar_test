<?php

namespace App\Tool;

use DateTime;

class SetDate
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setDate($chaine, $chaine2, $booking): void
    {
        $data = ['/Jan/', '/Feb/', '/Mar/', '/Apr/', '/May/', '/Jun/', '/Jul/', '/Aug/', '/Sep/', '/Oct/', '/Nov/', '/Dec/'];
        foreach ($data as $key => $value) {
            if (preg_match($value, $chaine) && preg_match($value, $chaine2)) {
                $final = substr_replace($chaine, $key + 1, 0, -17);
                $final2 = substr_replace($chaine2, $key + 1, 0, -17);
            }
        }
        $replace = preg_replace('/ /', '/', $final, 2);
        $datetime = strtotime($replace);
        $datetest = date('Y/m/d H:i:s', $datetime);
        $data = new DateTime($datetest);
        $dateValable = clone $data;
        $dateValable->modify('-2 hour');
        $replace2 = preg_replace('/ /', '/', $final2, 2);
        $datetime2 = strtotime($replace2);
        $datetest2 = date('Y/m/d H:i:s', $datetime2);
        $data2 = new DateTime($datetest2);
        $dateValable2 = clone $data2;
        $dateValable2->modify('-2 hour');
        if ($dateValable instanceof DateTime && $dateValable2 instanceof DateTime) {
            $booking->setBeginAt($dateValable);
            $booking->setEndAt($dateValable2);
            $this->entityManager->Add($booking);
        }
    }

    public function setOne($date): DateTime
    {
        $test = substr($date, 0, -1);
        $telephone = preg_replace('/[^0-9-:]/', '', $test);
        $ok = preg_replace('/([^[:space:]]{10})/', '\1 ', $telephone);
        $datetime = strtotime($ok);
        $datetest = date('Y/m/d H:i:s', $datetime);
        $data = new DateTime($datetest);

        return $data;
    }
}
