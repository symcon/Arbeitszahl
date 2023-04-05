<?php

declare(strict_types=1);
	class Jahresarbeitszahl extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger('thermalEnergieID',0);
			$this->RegisterPropertyInteger('electricEnergieID', 0);
			$this->RegisterVariableFloat('efficiency', 'Efficiency', '',0);
			$this->EnableAction('efficiency');

			//Create cyclic event
			if(@IPS_GetObjectIDByIdent('JAZEvent',$this->InstanceID) === false ){
				$eid = IPS_CreateEvent(1);
				IPS_SetParent($eid, $this->InstanceID);
				IPS_SetIdent($eid, 'JAZEvent');
				IPS_SetEventCyclicTimeFrom($eid, 23,59,59);
				IPS_SetEventAction($eid, '{28E92DFA-1640-2F3B-74F6-4B2AAE21CE22}', ['FUNCTION' =>'JAZ_Calculation']);
				IPS_SetEventActive($eid, true);
			}
			
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
			
			$this->Calculation();
		}

		public function Calculation()
		{
			$thermalID = $this->ReadPropertyInteger('thermalEnergieID');
			$electricID = $this->ReadPropertyInteger('electricEnergieID');
			if(!IPS_VariableExists($thermalID) && !IPS_VariableExists($electricID)){
				return;
			}

			$archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
			
			//Get the timestamps last midnight and 1 year ago
			$endTime = strtotime('midnight -1 sec');
			$startTime = strtotime('-1 year', $endTime);

			$thermalYear = AC_GetAggregatedValues($archiveID, $thermalID, 1/*Daily Aggregation */, $startTime, $endTime, 0);
			$electricYear = AC_GetAggregatedValues($archiveID, $electricID, 1/*Daily Aggregation */, $startTime, $endTime, 0);

			if(count($thermalYear) == 0 || count($electricYear) == 0){
				$this->SetStatus(201);
				return;
			}

			while (count($thermalYear) != count($electricYear)) {
				if (count($thermalYear) > count($electricYear)) {
					array_pop($thermalYear);
				}else {
					array_pop($electricYear);
				}
			}
			
			$thermalSum = array_sum(array_column($thermalYear,'Avg'));
			$electricSum = array_sum(array_column($electricYear,'Avg'));

			if($electricSum != 0){
				$efficiency = $thermalSum / $electricSum;
				$this->SendDebug('Success', print_r($efficiency),0);
				$this->SetValue('efficiency', $efficiency);
				$this->SetStatus(102);
			}else {
				$this->SendDebug('ERROR', 'Sum of Electrical Energy is ' + $electricSum,0);
				$this->SetStatus(202);
			}
		}
	}