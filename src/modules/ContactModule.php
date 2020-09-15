<?php

namespace hiapi\heppy\modules;

use err;

class ContactModule extends AbstractModule
{
    /** {@inheritdoc} */
    public $uris = [
        'contact' => 'urn:ietf:params:xml:ns:contact-1.0',
        'contact_hm' => 'http://hostmaster.ua/epp/contact-1.1',
    ];

    public $extURIs = [
        'namestoreExt' => 'http://www.verisign-grs.com/epp/namestoreExt-1.1',
    ];

    /** {@inheritdoc} */
    public function isAvailable() : bool
    {
        return !$this->isNamestoreExtensionEnabled();
    }

    /**
     * @param array $row
     * @return array
     */
    public function contactSet(array $row): array
    {
        if (!$this->isAvailable()) {
            return $row;
        }

        $row['epp_id'] = $this->fixContactID($row['epp_id']);

        try {
            $info = $this->tool->contactInfo($row);
        } catch (\Throwable $e) {
            return $this->contactCreate($row);
        }

        return $this->contactUpdate($row, $info);
    }

    /**
     * @param array $row
     * @return array
     */
    public function contactInfo(array $row): array
    {
        return $this->tool->commonRequest("{$this->object}:info", array_filter([
            'id'        => $this->fixContactID($row['epp_id']),
            'pw'        => $row['password'],
        ]), [
            'epp_id'        => 'id',
            'name'          => 'name',
            'organization'  => 'org',
            'password'      => 'pw',
            'email'         => 'email',
            'fax_phone'     => 'fax',
            'voice_phone'   => 'voice',
            'country'       => 'cc',
            'city'          => 'city',
            'org'           => 'org',
            'roid'          => 'roid',
            'postal_code'   => 'pc',
            'street1'       => 'street',
            'province'      => 'sp',
            'statuses'      => 'statuses',
        ]);
    }

    /**
     * @param array $row
     * @return array
     */
    public function contactCreate(array $row): array
    {
        return $this->tool->commonRequest("{$this->object}:create", array_filter([
            'id'        => $this->fixContactID($row['epp_id']),
            'name'      => $row['name'],
            'email'     => $row['email'],
            'voice'     => $row['voice_phone'],
            'fax'       => $row['fax_phone']    ?? null,
            'org'       => $row['org']          ?? null,
            'cc'        => $row['country']      ?? null,
            'city'      => $row['city']         ?? null,
            'street1'   => $row['street1']      ?? null,
            'street2'   => $row['street2']      ?? null,
            'street3'   => $row['street3']      ?? null,
            'pc'        => $row['postal_code']  ?? null,
            'pw'        => $row['password'] ?: $this->generatePassword(16),
        ], $this->getFilterCallback()), [
            'epp_id'        => 'id',
            'created_date'  => 'crDate',
        ]);
    }

    /**
     * @param array $row
     * @param array|null $info
     * @return array
     */
    public function contactUpdate(array $row, array $info): array
    {
        $row = $this->prepareDataForContactUpdate($row, $info);

        return $this->tool->commonRequest("{$this->object}:update", array_filter([
            'id'        => $row['epp_id'],
            'add'       => $row['add'] ?? null,
            'rem'       => $row['rem'] ?? null,
            'chg'       => $row['chg'] ?? null,
        ]), [], [
            'epp_id'    => $this->fixContactID($row['epp_id']),
        ]);
    }

    /**
     * @param array $row
     * @return array
     */
    public function contactDelete(array $row): array
    {
        return $this->tool->commonRequest("{$this->object}:delete", [
            'id'    => $this->fixContactID($row['epp_id']),
        ]);
    }

    /**
     * @param array $local
     * @param array $remote
     * @return array
     */
    private function prepareDataForContactUpdate(array $local, array $remote): array
    {
        $local['password'] = $local['password'] ?? $this->generatePassword();
        return $this->prepareDataForUpdate($local, $remote, [
            'name'          => 'name',
            'organization'  => 'org',
            'email'         => 'email',
            'fax_phone'     => 'fax',
            'voice_phone'   => 'voice',
            'country'       => 'cc',
            'city'          => 'city',
            'postal_code'   => 'pc',
            'street1'       => 'street1',
            'street2'       => 'street2',
            'street3'       => 'street3',
            'province'      => 'sp',
            'password'      => 'pw'
        ]);
    }

}
