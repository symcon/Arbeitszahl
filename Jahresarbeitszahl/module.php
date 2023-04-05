<?php

declare(strict_types=1);
    class Jahresarbeitszahl extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyInteger('thermalEnergieID', 0);
            $this->RegisterPropertyInteger('electricEnergieID', 0);
            $this->RegisterVariableFloat('efficiencyJAZ', 'Efficiency year', '', 0);
            $this->RegisterVariableFloat('efficiencyMAZ', 'Efficiency month', '', 0);
            $this->EnableAction('efficiencyJAZ');
            $this->EnableAction('efficiencyMAZ');

            $this->RegisterTimer('ARZ_Calculation', (strtotime('tomorrow') - time()) * 1000, 'ARZ_Calculation($_IPS[\'TARGET\']);');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();

            $thermalID = $this->ReadPropertyInteger('thermalEnergieID');
            $electricID = $this->ReadPropertyInteger('electricEnergieID');

            if (!IPS_VariableExists($thermalID) && !IPS_VariableExists($electricID)) {
                return;
            }
            $this->Calculation();
        }

        public function Calculation()
        {
            $thermalID = $this->ReadPropertyInteger('thermalEnergieID');
            $electricID = $this->ReadPropertyInteger('electricEnergieID');

            if (!IPS_VariableExists($thermalID) && !IPS_VariableExists($electricID)) {
                return;
            }

            //Get the timestamps last midnight and 1 year ago
            $endTime = strtotime('midnight -1 sec');
            $startTime = strtotime('-1 year', $endTime);

            $year = $this->getLoggedValues($startTime, $endTime, $thermalID, $electricID);
            if ($this->GetStatus() > 200) {
                return;
            }
            $thermalYear = $year[0];
            $electricYear = $year[1];
            $thermalSum = array_sum(array_column($thermalYear, 'Avg'));
            $electricSum = array_sum(array_column($electricYear, 'Avg'));

            if ($electricSum != 0) {
                $efficiency = $thermalSum / $electricSum;
                //$this->SendDebug('Success', $efficiency, 0);
                $this->SetValue('efficiencyJAZ', $efficiency);
                $this->SetStatus(102);
            } else {
                $this->SendDebug('ERROR', 'Sum of Year Electrical Energy is 0', 0);
                $this->SetStatus(202);
                return;
            }

            //Get the timestamps last midnight and 1 month ago
            $endTime = strtotime('midnight -1 sec');
            $startTime = strtotime('-1 month', $endTime);

            $month = $this->getLoggedValues($startTime, $endTime, $thermalID, $electricID);
            if ($this->GetStatus() > 200) {
                return;
            }
            $thermalMonth = $month[0];
            $electricMonth = $month[1];
            $thermalSum = array_sum(array_column($thermalMonth, 'Avg'));
            $electricSum = array_sum(array_column($electricMonth, 'Avg'));

            if ($electricSum != 0) {
                $efficiency = $thermalSum / $electricSum;
                //$this->SendDebug('Success', print_r($efficiency), 0);
                $this->SetValue('efficiencyMAZ', $efficiency);
                $this->SetStatus(102);
            } else {
                $this->SendDebug('ERROR', 'Sum of Month Electrical Energy is 0', 0);
                $this->SetStatus(202);
                return;
            }

            $this->SetTimerInterval('ARZ_Calculation', (strtotime('tomorrow') - time()) * 1000);
        }

        private function getLoggedValues(int $startTime, int $endTime, int $thermalID, int $electricID)
        {
            $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

            $thermal = AC_GetAggregatedValues($archiveID, $thermalID, 1/*Daily Aggregation */, $startTime, $endTime, 0);
            $electric = AC_GetAggregatedValues($archiveID, $electricID, 1/*Daily Aggregation */, $startTime, $endTime, 0);

            if (count($thermal) == 0 || count($electric) == 0) {
                $this->SetStatus(201);
                return;
            }

            while (count($thermal) != count($electric)) {
                if (count($thermal) > count($electric)) {
                    array_pop($thermal);
                } else {
                    array_pop($electric);
                }
            }

            return [$thermal, $electric];
        }
    }