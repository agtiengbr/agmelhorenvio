<?php

require_once 'AgMelhorEnvioCommunicator.php';

class AgMelhorEnvioAgenciesGetter extends AgMelhorEnvioCommunicato
{
	public function get()
	{
		$response = $this->doRequest('GET', 'shipment/agencies');
        $parsed_response = json_decode($response);

        if (!is_array($parsed_response)) {
            if (is_object($parsed_response) && isset($parsed_response->data) && is_array($parsed_response->data)) {
                $parsed_response = $parsed_response->data;
            } else {
                return array();
            }
        }

        $return = array();

        foreach ($parsed_response as $agency) {
            if (!is_object($agency)) {
                continue;
            }

            $obj = new AgMelhorEnvioRemoteAgency;

            $address = new AgMelhorEnvioRemoteAddress;
            $address->setAddress($agency->address?->address);
            $address->setCity($agency->address?->city?->city);
            $address->setUf($agency->address?->city?->state?->state_abbr);

            $obj->setId($agency->id ?? null)
            	->setName($agency->name ?? null)
            	->setInitials($agency->initials ?? null)
				->setCode($agency->code ?? null)
            	->setCompanyName($agency->company_name ?? null)
            	->setStatus($agency->status ?? null)
            	->setEmail($agency->email ?? null)
            	->setAddress($address);

			$companies = [];
            $agencyCompanies = $agency->companies ?? [];
            if (is_object($agencyCompanies)) {
                $agencyCompanies = (array) $agencyCompanies;
            }
            if (is_array($agencyCompanies)) {
                foreach ($agencyCompanies as $company) {
                    if (!is_object($company)) {
                        continue;
                    }
                    $companies[] = (new AgMelhorEnvioCompany)
                        ->setId($company->id ?? null)
                        ->setName($company->name ?? null)
                        ->setHasGroupedVolumes($company->has_grouped_volumes ?? null)
                        ->setAvailable($company->available ?? null)
                        ->setStatus($company->status ?? null)
                        ->setPicture($company->picture ?? null)
                        ->setUseOwnContract($company->use_own_contract ?? null)
                        ->setBatchSize($company->batch_size ?? null);
                }
            }

			$obj->setCompanies($companies);
         	$return[] = $obj;
        }

        return $return;
	}
}
