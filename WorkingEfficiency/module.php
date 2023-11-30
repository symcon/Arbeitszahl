<?php

declare(strict_types=1);
class WorkingEfficiency extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger('ThermalEnergieID', 0);
        $this->RegisterPropertyInteger('ElectricEnergieID', 0);
        $this->RegisterVariableFloat('EfficiencyMAZ', $this->Translate('SPF Month'), '', 0);
        $this->RegisterVariableFloat('EfficiencyJAZ', $this->Translate('SPF Year'), '', 0);

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

        $thermalID = $this->ReadPropertyInteger('ThermalEnergieID');
        $electricID = $this->ReadPropertyInteger('ElectricEnergieID');

        if (!IPS_VariableExists($thermalID) || !IPS_VariableExists($electricID)) {
            return;
        }
        $this->Calculation();
    }

    public function Calculation()
    {
        $thermalID = $this->ReadPropertyInteger('ThermalEnergieID');
        $electricID = $this->ReadPropertyInteger('ElectricEnergieID');

        if (!IPS_VariableExists($thermalID) || !IPS_VariableExists($electricID)) {
            return;
        }

        //Get the timestamps last midnight and 1 year ago
        $endTime = strtotime('midnight -1 sec');
        $startTime = strtotime('-1 year', $endTime);

        $year = $this->getAggregatedValuesTimeBased($startTime, $endTime, $thermalID, $electricID);
        if ($this->GetStatus() > 200) {
            return;
        }
        $thermalYear = $year[0];
        $electricYear = $year[1];
        $thermalSum = array_sum(array_column($thermalYear, 'Avg'));
        $electricSum = array_sum(array_column($electricYear, 'Avg'));

        if ($electricSum != 0) {
            $efficiency = $thermalSum / $electricSum;
            $this->SendDebug('Success Efficiency Year', $efficiency, 0);
            $this->SetValue('EfficiencyJAZ', $efficiency);
            $this->SetStatus(102);
        } else {
            $this->SendDebug('ERROR', 'Sum of Year Electrical Energy is 0', 0);
            $this->SetStatus(202);
            return;
        }

        //Get the timestamps last midnight and 1 month ago
        $endTime = strtotime('midnight -1 sec');
        $startTime = strtotime('-1 month', $endTime);

        $month = $this->getAggregatedValuesTimeBased($startTime, $endTime, $thermalID, $electricID);
        if ($this->GetStatus() > 200) {
            return;
        }
        $thermalMonth = $month[0];
        $electricMonth = $month[1];
        $thermalSum = array_sum(array_column($thermalMonth, 'Avg'));
        $electricSum = array_sum(array_column($electricMonth, 'Avg'));

        if ($electricSum != 0) {
            $efficiency = $thermalSum / $electricSum;
            $this->SendDebug('Success Efficiency Month', $efficiency, 0);
            $this->SetValue('EfficiencyMAZ', $efficiency);
            $this->SetStatus(102);
        } else {
            $this->SendDebug('ERROR', 'Sum of Month Electrical Energy is 0', 0);
            $this->SetStatus(202);
            return;
        }

        $this->SetTimerInterval('ARZ_Calculation', (strtotime('tomorrow') - time()) * 1000);
    }

    private function getAggregatedValuesTimeBased(int $startTime, int $endTime, int $thermalID, int $electricID)
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