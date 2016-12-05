<?php
/*
 * This file is part of github-overview.
 *
 * (c) Sebastian Bauer <i@sebastianbauer.name>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * GitHub facade.
 */
class GitHubFacade
{
    /**
     * @var array The settings array.
     */
    private $settings = null;

    /**
     * @var \Github\Client The GitHub client.
     */
    private $client = null;

    /**
     * Standard constructor of the GitHub client.
     *
     * @param array $settings The settings for this facade.
     *
     * Settings parameter array should include the following values:
     *  - token: 'Your GitHub token'
     *  - organisation: 'The name of your organisation on GitHub'
     *  - perPage: The number of items per page - used for the pager, you should set it to a value more than the number
     *             than the repository count of your organisation.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;

        $this->createClient();
    }

    /**
     * Fetch all repositories, which are connected to the set organisation.
     *
     * @return array All repositories, which are connected to the set organisation
     */
    public function fetchAllRepositories()
    {
        $organizationApi = $this->client->api('organization');

        $pager = new Github\ResultPager($this->client);
        $parameters = array($this->getSetting('organisation'));
        $repositories = $pager->fetchAll($organizationApi, 'repositories', $parameters);

        $repositoryNames = array();

        foreach ($repositories as $repository) {
            $repositoryNames[] = $repository['name'];
        }

        sort($repositoryNames);

        return $repositoryNames;
    }

    /**
     * Fetch all pull requests of the repository, represented by the given name.
     *
     * @param string $repositoryName The name of the repository, for which we want all the pull requests.
     *
     * @return array All pull request objects of the given repository.
     */
    public function fetchPullRequests($repositoryName)
    {
        $pullRequestsApi = $this->client->api('pull_request');
        $pullRequestsApi->setPerPage($this->getSetting('perPage'));

        $pager = new Github\ResultPager($this->client);
        $parameters = array($this->getSetting('organisation'), $repositoryName);

        return $pager->fetchAll($pullRequestsApi, 'all', $parameters);
    }

    /**
     * Create the GitHub client.
     */
    protected function createClient()
    {
        $this->client = new \Github\Client();
        $this->client->authenticate($this->getSetting('token'), '', Github\Client::AUTH_HTTP_TOKEN);
    }

    /**
     * Get the setting with the given key.
     *
     * @param string $key The key of the wanted settings.
     *
     * @return array|string The setting behind the given key.
     */
    protected function getSetting($key)
    {
        return $this->settings[$key];
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    protected function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return \Github\Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @param \Github\Client $client
     */
    protected function setClient($client)
    {
        $this->client = $client;
    }

}

