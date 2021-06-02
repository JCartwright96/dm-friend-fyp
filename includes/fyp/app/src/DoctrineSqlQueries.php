<?php


namespace Fyp;
use Doctrine\DBAL\DriverManager;

class DoctrineSqlQueries
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /*
     * --- USER PROFILE QUERIES ---
     */

    public function queryCheckUserExists($conn, $queryBuilder, $cleaned_parameters)
    {

        // This query is used during signup, as it contains an email being checked
        if (isset($cleaned_parameters['email'])) {
            $username = $cleaned_parameters['username'];
            $email = $cleaned_parameters['email'];
            $queryBuilder = $queryBuilder->select('*')
                ->from('users')
                ->where('username = \'' . $username . '\'')
                ->orWhere('email = \'' . $email . '\'');
        } // Otherwise this query is used during login & delete, as it only needs to check based on username
        else {
            $username = $cleaned_parameters['username'];
            $queryBuilder = $queryBuilder->select('username, password')
                ->from('users')
                ->where('username = \'' . $username . '\'');
        }

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryCreateNewUser($app, $queryBuilder, $cleaned_parameters)
    {
        $email = $cleaned_parameters['email'];
        $username = $cleaned_parameters['username'];
        $password = $cleaned_parameters['hashed_password'];

        $queryBuilder = $queryBuilder->insert('users')
            ->values([
                'username' => ':username',
                'email' => ':email',
                'password' => ':password',
            ])->setParameters([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /*
     *  -- ENEMY RELATED QUERIES --
     */

    /**
     * @param $conn
     * @param $queryBuilder
     * @param $cleaned_parameters
     * @param $current_user
     * @return mixed
     */

    public function queryCheckEnemyExists($conn, $queryBuilder, $cleaned_parameters, $current_user)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $enemy_name = ($cleaned_parameters['enemy_name']);
        $username = $current_user;
        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->where('enemy_name = \'' . $enemy_name . '\'')
            ->andWhere('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetUserDetails($conn, $queryBuilder, $current_user)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $queryBuilder = $queryBuilder->select('email, username')
            ->from('users')
            ->where('username = \'' . $username . '\'');

        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }


    public function queryGetEnemyId($conn, $queryBuilder, $current_user, $current_enemy)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $enemy_name = $current_enemy;
        $queryBuilder = $queryBuilder->select('enemy_id')
            ->from('enemies')
            ->where('enemy_name = \'' . $enemy_name . '\'')
            ->andWhere('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryCreateNewEnemy($conn, $queryBuilder, $cleaned_parameters, $current_user)
    {
        // Enemy Details
        $enemy_name = $cleaned_parameters['enemy_name'];
        $enemy_ac = $cleaned_parameters['enemy_ac'];
        $enemy_speed = $cleaned_parameters['enemy_speed'];
        $enemy_hp = $cleaned_parameters['enemy_hp'];
        $username = $current_user;

        // Enemy abilities
        if (isset($cleaned_parameters['strength'])) {
            $strength = $cleaned_parameters['strength'];
        } else $strength = null;

        if (isset($cleaned_parameters['dexterity'])) {
            $dexterity = $cleaned_parameters['dexterity'];
        } else $dexterity = null;

        if (isset($cleaned_parameters['constitution'])) {
            $constitution = $cleaned_parameters['constitution'];
        } else $constitution = null;

        if (isset($cleaned_parameters['intelligence'])) {
            $intelligence = $cleaned_parameters['intelligence'];
        } else $intelligence = null;

        if (isset($cleaned_parameters['wisdom'])) {
            $wisdom = $cleaned_parameters['wisdom'];
        } else $wisdom = null;

        if (isset($cleaned_parameters['charisma'])) {
            $charisma = $cleaned_parameters['charisma'];
        } else $charisma = null;

        $queryBuilder = $queryBuilder->insert('enemies')
            ->values([
                'enemy_name' => ':enemy_name',
                'enemy_ac' => ':enemy_ac',
                'enemy_hp' => ':enemy_hp',
                'enemy_speed' => ':enemy_speed',
                'username' => ':username',
                'strength' => ':strength',
                'dexterity' => ':dexterity',
                'constitution' => ':constitution',
                'intelligence' => ':intelligence',
                'wisdom' => ':wisdom',
                'charisma' => ':charisma'
            ])->setParameters([
                ':enemy_name' => $enemy_name,
                ':enemy_ac' => $enemy_ac,
                ':enemy_hp' => $enemy_hp,
                ':enemy_speed' => $enemy_speed,
                ':username' => $username,
                ':strength' => $strength,
                ':dexterity' => $dexterity,
                ':constitution' => $constitution,
                ':intelligence' => $intelligence,
                ':wisdom' => $wisdom,
                ':charisma' => $charisma
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }


    public function queryUpdateEnemy($conn, $queryBuilder, $current_enemy_id, $cleaned_parameters, $current_user)
    {
        // Enemy Details
        $enemy_id = $current_enemy_id;
        $enemy_name = $cleaned_parameters['enemy_name'];
        $enemy_ac = $cleaned_parameters['enemy_ac'];
        $enemy_speed = $cleaned_parameters['enemy_speed'];
        $enemy_hp = $cleaned_parameters['enemy_hp'];
        $username = $current_user;


        // Enemy abilities
        if (isset($cleaned_parameters['strength'])) {
            $strength = $cleaned_parameters['strength'];
        } else $strength = null;

        if (isset($cleaned_parameters['dexterity'])) {
            $dexterity = $cleaned_parameters['dexterity'];
        } else $dexterity = null;

        if (isset($cleaned_parameters['constitution'])) {
            $constitution = $cleaned_parameters['constitution'];
        } else $constitution = null;

        if (isset($cleaned_parameters['intelligence'])) {
            $intelligence = $cleaned_parameters['intelligence'];
        } else $intelligence = null;

        if (isset($cleaned_parameters['wisdom'])) {
            $wisdom = $cleaned_parameters['wisdom'];
        } else $wisdom = null;

        if (isset($cleaned_parameters['charisma'])) {
            $charisma = $cleaned_parameters['charisma'];
        } else $charisma = null;

        $queryBuilder = $queryBuilder->update('enemies')
            ->set('enemy_name', ':enemy_name')
            ->set('enemy_hp', ':enemy_hp')
            ->set('enemy_ac', ':enemy_ac')
            ->set('enemy_speed', ':enemy_speed')
            ->set('strength', ':strength')
            ->set('dexterity', ':dexterity')
            ->set('constitution', ':constitution')
            ->set('intelligence', ':intelligence')
            ->set('wisdom', ':wisdom')
            ->set('charisma', ':charisma')
            ->where('enemy_id = \'' . $enemy_id . '\'')
            ->andWhere('username = \'' . $username . '\'')
            ->setParameters([
                ':enemy_name' => $enemy_name,
                ':enemy_ac' => $enemy_ac,
                ':enemy_hp' => $enemy_hp,
                ':enemy_speed' => $enemy_speed,
                ':strength' => $strength,
                ':dexterity' => $dexterity,
                ':constitution' => $constitution,
                ':intelligence' => $intelligence,
                ':wisdom' => $wisdom,
                ':charisma' => $charisma
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }



    /*
     *  -- ACTION RELATED QUERIES --
     */

    public function queryCheckActionExists($conn, $queryBuilder, $cleaned_parameters, $current_user, $current_enemy_id)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $action_name = ($cleaned_parameters['action_name']);
        $username = $current_user;
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->select('*')
            ->from('actions')
            ->where('action_name = \'' . $action_name . '\'')
            ->andWhere('username = \'' . $username . '\'')
            ->andWhere('enemy_id = \'' . $enemy_id . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetCurrentEnemyActions($conn, $queryBuilder, $current_user, $current_enemy_id)
    {
        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->select('*')
            ->from('actions')
            ->andWhere('username = \'' . $username . '\'')
            ->andWhere('enemy_id = \'' . $enemy_id . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryGetActionDetails($conn, $queryBuilder, $current_action_id, $current_user)
    {
        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $action_id = $current_action_id;
        $queryBuilder = $queryBuilder->select('*')
            ->from('actions')
            ->where('action_id = \'' . $action_id . '\'')
            ->andWhere('username = \'' . $username . '\'');

        $queryBuilder->execute();

        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }


    public function queryAddNewAction($conn, $queryBuilder, $cleaned_parameters, $current_user, $current_enemy_id)
    {

        // Action Details
        $action_name = $cleaned_parameters['action_name'];
        $action_reach = $cleaned_parameters['action_reach'];
        $action_area = $cleaned_parameters['action_area'];
        $action_hit = $cleaned_parameters['action_hit'];
        $action_damage = $cleaned_parameters['action_damage'];
        $action_modifier = $cleaned_parameters['action_modifier'];

        // Current user and enemy the action is for
        $username = $current_user;
        $enemy_id = $current_enemy_id;

        // inserting into "actions" table
        $queryBuilder = $queryBuilder->insert('actions')
            ->values([
                'action_name' => ':action_name',
                'reach' => ':reach',
                'area' => ':area',
                'hit_chance' => ':hit_chance',
                'damage' => ':damage',
                'modifier' => ':modifier',
                'enemy_id' => ':enemy_id',
                'username' => ':username',
            ])->setParameters([

                ':action_name' => $action_name,
                ':reach' => $action_reach,
                ':area' => $action_area,
                ':hit_chance' => $action_hit,
                ':damage' => $action_damage,
                ':modifier' => $action_modifier,
                ':enemy_id' => $enemy_id,
                ':username' => $username,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryUpdateAction($conn, $queryBuilder, $current_action_id, $cleaned_parameters, $current_user)
    {
        // Enemy Details
        $action_id = $current_action_id;
        $action_name = $cleaned_parameters['action_name'];
        $action_reach = $cleaned_parameters['action_reach'];
        $action_area = $cleaned_parameters['action_area'];
        $action_hit = $cleaned_parameters['action_hit'];
        $action_damage = $cleaned_parameters['action_damage'];
        $action_modifier = $cleaned_parameters['action_modifier'];
        $username = $current_user;

        $queryBuilder = $queryBuilder->update('actions')
            ->set('action_name', ':action_name')
            ->set('reach', ':reach')
            ->set('hit_chance', ':hit_chance')
            ->set('area', ':area')
            ->set('damage', ':damage')
            ->set('modifier', ':modifier')
            ->where('action_id = \'' . $action_id . '\'')
            ->andWhere('username = \'' . $username . '\'')
            ->setParameters([
                ':action_name' => $action_name,
                ':reach' => $action_reach,
                ':area' => $action_area,
                ':hit_chance' => $action_hit,
                ':damage' => $action_damage,
                ':modifier' => $action_modifier,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /*
     * --- ENCOUNTER RELATED QUERIES
     */

    public function queryCheckEncounterExists($conn, $queryBuilder, $cleaned_parameters, $current_user)
    {
        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $encounter_name = ($cleaned_parameters['encounter_name']);
        $username = $current_user;
        $queryBuilder = $queryBuilder->select('*')
            ->from('encounters')
            ->where('encounter_name = \'' . $encounter_name . '\'')
            ->andWhere('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryAddNewEncounter($conn, $queryBuilder, $cleaned_parameters, $current_user)
    {
        // Action Details
        $encounter_name = $cleaned_parameters['encounter_name'];
        $encounter_location = $cleaned_parameters['encounter_location'];
        $encounter_description = $cleaned_parameters['encounter_description'];
        $encounter_notes = $cleaned_parameters['encounter_notes'];

        $username = $current_user;

        // Current user and enemy the action is for

        // inserting into "actions" table
        $queryBuilder = $queryBuilder->insert('encounters')
            ->values([
                'encounter_name' => ':encounter_name',
                'location' => ':location',
                'description' => ':description',
                'notes' => ':notes',
                'username' => ':username',
            ])->setParameters([

                ':encounter_name' => $encounter_name,
                ':location' => $encounter_location,
                ':description' => $encounter_description,
                ':notes' => $encounter_notes,
                ':username' => $username,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryUpdateEncounter($conn, $queryBuilder, $current_encounter_id, $cleaned_parameters, $current_user)
    {
        // Encounter Details
        $encounter_name = $cleaned_parameters['encounter_name'];
        $encounter_location = $cleaned_parameters['encounter_location'];
        $encounter_description = $cleaned_parameters['encounter_description'];
        $encounter_notes = $cleaned_parameters['encounter_notes'];

        $username = $current_user;
        $encounter_id  = $current_encounter_id;

        $queryBuilder = $queryBuilder->update('encounters')
            ->set('encounter_name', ':encounter_name')
            ->set('location', ':location')
            ->set('description', ':description')
            ->set('notes', ':notes')
            ->where('encounter_id = \'' . $encounter_id . '\'')
            ->andWhere('username = \'' . $username . '\'')
            ->setParameters([
                ':encounter_name' => $encounter_name,
                ':location' => $encounter_location,
                ':description' => $encounter_description,
                ':notes' => $encounter_notes,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryGetEncounterId($conn, $queryBuilder, $current_user, $current_encounter)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $encounter_name = $current_encounter;
        $queryBuilder = $queryBuilder->select('encounter_id')
            ->from('encounters')
            ->where('encounter_name = \'' . $encounter_name . '\'')
            ->andWhere('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetActionId($conn, $queryBuilder, $current_user, $current_action)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $action_name = $current_action;
        $queryBuilder = $queryBuilder->select('action_id')
            ->from('actions')
            ->where('action_name = \'' . $action_name . '\'')
            ->andWhere('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryFetchEncounterDetails($conn, $queryBuilder, $current_encounter_id, $current_user)
    {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;
        $encounter_id = $current_encounter_id;
        $queryBuilder = $queryBuilder->select('*')
            ->from('encounters')
            ->where('encounter_id = \'' . $encounter_id . '\'')
            ->andWhere('username = \'' . $username . '\'');

        $queryBuilder->execute();

        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetEnemiesInEncounter($conn, $queryBuilder, $current_encounter_id, $current_user){

        $username = $current_user;
        $encounter_id = $current_encounter_id;

        $queryBuilder = $queryBuilder->select('enemy_id, enemy_quantity')
            ->from('appearances')
            ->where('encounter_id =\'' . $encounter_id . '\'')
            ->andWhere('username = \'' . $username . '\'');

        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }


    /*
     * -- DISPLAY RELATED QUERIES (Pulling enemies to display etc) --
     */

    public function queryGetRecentEnemies($conn, $queryBuilder, $current_user) {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;

        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->Where('username = \'' . $username . '\'')
            ->orderBy('enemy_id', 'DESC')
            ->setMaxResults(2);

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryGetRecentEncounters($conn, $queryBuilder, $current_user) {

        // Select all from the table row where the username is current user and enemy name is the enemy being searched for
        $username = $current_user;

        $queryBuilder = $queryBuilder->select('*')
            ->from('encounters')
            ->Where('username = \'' . $username . '\'')
            ->orderBy('encounter_id', 'DESC')
            ->setMaxResults(2);

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryGetUserEnemies($conn, $queryBuilder, $current_user) {

        $username = $current_user;
        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->where('username = \'' . $username . '\'')
            ->orderBy('enemy_name', 'ASC');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryGetDefaultEnemies($conn, $queryBuilder) {

        $username = "default_enemy";
        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->where('username = \'' . $username . '\'')
            ->orderBy('enemy_name', 'ASC');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }


    public function queryGetSearchedEnemy($conn, $queryBuilder, $current_user, $searched_enemy) {

        $username = $current_user;
        $enemy_name = $searched_enemy;
        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
//            ->where('username = \' :username \'')
            ->where('username = \'' . $username . '\'')
            ->andWhere('enemy_name LIKE \'%' . $enemy_name . '%\'' );
//            ->andWhere('enemy_name LIKE \'%:enemy_name%\'')
//            ->setParameters([
//                ":username" => $username,
//                ":enemy_name" => $enemy_name
//            ]);
//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryFetchEnemyDetails($conn, $queryBuilder, $current_enemy_id, $current_user) {
        $username = $current_user;
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->where('enemy_id = \'' . $enemy_id . '\'')
            ->andWhere('username = \'' . $username  . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetEnemyDetailsId($conn, $queryBuilder, $current_enemy_id) {
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->select('*')
            ->from('enemies')
            ->where('enemy_id = \'' . $enemy_id . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryGetUserEncounters($conn, $queryBuilder, $current_user) {

        $username = $current_user;
        $queryBuilder = $queryBuilder->select('*')
            ->from('encounters')
            ->where('username = \'' . $username . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetchAll();
    }

    public function queryCheckEnemyExistsInEncounter($conn, $queryBuilder, $current_encounter_id, $current_enemy_id, $current_user) {

        $username = $current_user;
        $encounter_id = $current_encounter_id;
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->select('enemy_quantity')
            ->from('appearances')
            ->where('username = \'' . $username . '\'')
            ->andWhere('encounter_id = \'' . $encounter_id . '\'')
            ->andwhere('enemy_id = \'' . $enemy_id . '\'');

//        $queryBuilder->execute();
//        return $queryBuilder->getResult();
        $stmt = $conn->query($queryBuilder->getSQL());
        return $stmt->fetch();
    }

    public function queryAddEnemyToEncounter($conn, $queryBuilder, $current_encounter_id, $current_enemy_id, $quantity, $current_user)
    {

        // Action Details
        $encounter_id = $current_encounter_id;
        $enemy_id = $current_enemy_id;
        $enemy_quantity = $quantity;
        $username = $current_user;

        // Current user and enemy the action is for

        // inserting into "actions" table
        $queryBuilder = $queryBuilder->insert('appearances')
            ->values([
                'encounter_id' => ':encounter_id',
                'enemy_id' => ':enemy_id',
                'enemy_quantity' => ':enemy_quantity',
                'username' => ':username',
            ])->setParameters([
                ':encounter_id' => $encounter_id,
                ':enemy_id' => $enemy_id,
                ':enemy_quantity' => $enemy_quantity,
                ':username' => $username,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryUpdateEnemyQuantity($conn, $queryBuilder, $current_encounter_id, $current_enemy_id, $new_quantity, $current_user)
    {

        // Action Details
        $encounter_id = $current_encounter_id;
        $enemy_id = $current_enemy_id;
        $quantity = $new_quantity;
        $username = $current_user;

        // Current user and enemy the action is for

        // inserting into "appearances" table
        $queryBuilder = $queryBuilder->update('appearances')
            ->set('enemy_quantity',':enemy_quantity')
            ->Where('encounter_id = \'' . $encounter_id . '\'')
            ->andWhere('enemy_id = \'' . $enemy_id . '\'')
            ->andwhere('username = \'' . $username . '\'')
            ->setParameters([
                ':enemy_quantity' => $quantity,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /*
     * -- Queries Handling Deletions --
     */

    public function queryDeleteEnemyActions($conn, $queryBuilder, $enemy_to_delete, $current_user)
    {
        // Action Details
        $enemy_id = $enemy_to_delete;
        $username = $current_user;

        // inserting into "appearances" table
        $queryBuilder = $queryBuilder->delete('actions')
            ->where('enemy_id = \'' . $enemy_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryDeleteEnemyAppearances($conn, $queryBuilder, $enemy_to_delete, $current_user)
    {
        // Action Details
        $enemy_id = $enemy_to_delete;
        $username = $current_user;

        $queryBuilder = $queryBuilder->delete('appearances')
            ->where('enemy_id = \'' . $enemy_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryDeleteEnemy($conn, $queryBuilder, $enemy_to_delete, $current_user)
    {
        // Action Details
        $enemy_id = $enemy_to_delete;
        $username = $current_user;

        $queryBuilder = $queryBuilder->delete('enemies')
            ->where('enemy_id = \'' . $enemy_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryDeleteEncounterAppearances($conn, $queryBuilder, $encounter_to_delete, $current_user)
    {
        // Action Details
        $encounter_id = $encounter_to_delete;
        $username = $current_user;

        $queryBuilder = $queryBuilder->delete('appearances')
            ->where('encounter_id = \'' . $encounter_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryRemoveEnemyFromEncounter($conn, $queryBuilder, $current_encounter_id, $current_enemy_id)
    {
        // Action Details
        $encounter_id = $current_encounter_id;
        $enemy_id = $current_enemy_id;

        $queryBuilder = $queryBuilder->delete('appearances')
            ->where('encounter_id = \'' . $encounter_id . '\'')
            ->andwhere('enemy_id = \'' . $enemy_id . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }


    public function queryDeleteEncounter($conn, $queryBuilder, $encounter_to_delete, $current_user)
    {
        // Action Details
        $encounter_id = $encounter_to_delete;
        $username = $current_user;

        $queryBuilder = $queryBuilder->delete('encounters')
            ->where('encounter_id = \'' . $encounter_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryDeleteAction($conn, $queryBuilder, $action_to_delete, $current_user)
    {
        // Action Details
        $action_id = $action_to_delete;
        $username = $current_user;

        $queryBuilder = $queryBuilder->delete('actions')
            ->where('action_id = \'' . $action_id . '\'')
            ->andwhere('username = \'' . $username . '\'');

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    public function queryDeleteUser($conn, $queryBuilder, $current_user)
    {
        // Action Details
        $username = $current_user;

        // delete all appearances belonging to user
        $queryBuilder = $queryBuilder->delete('appearances')
            ->where('username = \'' . $username . '\'');

        $store_result['delete_appearances_outcome'] = $queryBuilder->execute();

        // delete all actions created by user
        $queryBuilder = $queryBuilder->delete('actions')
            ->where('username = \'' . $username . '\'');

        $store_result['delete_actions_outcome'] = $queryBuilder->execute();

        // delete all encounters created by user
        $queryBuilder = $queryBuilder->delete('encounters')
            ->where('username = \'' . $username . '\'');

        $store_result['delete_encounters_outcome'] = $queryBuilder->execute();

        // delete all enemies created by user
        $queryBuilder = $queryBuilder->delete('enemies')
            ->where('username = \'' . $username . '\'');

        $store_result['delete_enemies_outcome'] = $queryBuilder->execute();

        // Delete user account
        $queryBuilder = $queryBuilder->delete('users')
            ->where('username = \'' . $username . '\'');

        $store_result['delete_user_outcome'] = $queryBuilder->execute();

        return $store_result;
    }

    public function queryChangeUserPassword($conn, $queryBuilder, $new_password, $current_user)
    {
        // Encounter Details

        $username = $current_user;
        $password  = $new_password;

        $queryBuilder = $queryBuilder->update('users')
            ->set('password', ':password')
            ->andWhere('username = \'' . $username . '\'')
            ->setParameters([
                ':password' => $password,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }
}


