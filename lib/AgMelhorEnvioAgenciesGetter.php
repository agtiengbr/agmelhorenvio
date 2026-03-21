<?php

require_once 'AgMelhorEnvioCommunicator.php';

class AgMelhorEnvioAgenciesGetter extends AgMelhorEnvioCommunicator
{
	public function get()
	{
		$response = $this->doRequest('GET', 'shipment/agencies');
        $parsed_response = json_decode($response);

        $return = array();

        foreach ($parsed_response as $agency) {
            $obj = new AgMelhorEnvioRemoteAgency;

            $address = new AgMelhorEnvioRemoteAddress;
            $address->setAddress(@$agency->address->address);
            $address->setCity(@$agency->address->city->city);
            $address->setUf(@$agency->address->city->state->state_abbr);

            $obj->setId($agency->id)
            	->setName($agency->name)
            	->setInitials($agency->initials)
				->setCode($agency->code)
            	->setCompanyName($agency->company_name)
            	->setStatus($agency->status)
            	->setEmail($agency->email)
            	->setAddress($address);

			$companies = [];
			foreach ($agency->companies as $company) {
				$companies[] = (new AgMelhorEnvioCompany)
					->setId($company->id)
					->setName($company->name)
					->setHasGroupedVolumes($company->has_grouped_volumes)
					->setAvailable($company->available)
					->setStatus($company->status)
					->setPicture($company->picture)
					->setUseOwnContract($company->use_own_contract)
					->setBatchSize($company->batch_size);
			}

			$obj->setCompanies($companies);
         	$return[] = $obj;   
        }

        return $return;
	}
}