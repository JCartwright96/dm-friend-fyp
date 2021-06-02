
# create default_enemy user account
INSERT INTO users(username, email, password)
values("default_enemy", "default_enemy@dmfriend.com", "$2y$10$0miKcd9K0OgxohHAi6V/8ufTqj236lSSf7zQ2HSGPSJxXdFyM/I4C");

# goblin
INSERT INTO enemies(enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("Goblin", "15", "7", "30", "8", "14", "10", "10", "8", "8", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Scimitar", "+4", "5", "", "1d6", "+2", "1", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Shortbow", "+4", "80/320", "", "1d6", "+2", "1", "default_enemy");

# Skeleton
INSERT INTO enemies(enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("Skeleton", "13", "13", "30", "10", "14", "15", "6", "8", "5", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Shortsword", "+4", "5", "", "1d6", "+2", "2", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Shortbow", "+4", "80/320", "", "1d6", "+2", "2", "default_enemy");

# wolf
INSERT INTO enemies(enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("Wolf", "13", "11", "40", "12", "15", "12", "3", "12", "6", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Bite", "+4", "5", "", "2d4", "+2", "3", "default_enemy");

# gnoll
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("4", "Gnoll", "15", "22", "30", "14", "12", "11", "6", "10", "7", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Bite", "+4", "5", "", "1d4", "+2", "4", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Spear", "+4", "5", "", "1d6/1d8", "+2", "4", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Spear (ranged)", "+4", "20/60", "", "1d6", "+2", "4", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Longbow", "+3", "150/600", "", "1d8", "+1", "4", "default_enemy");

# Bandit
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("5", "Bandit", "12", "11", "30", "11", "12", "12", "10", "10", "10", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Scimitar", "+3", "5", "", "1d6", "+1", "5", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Light Crossbow", "+3", "80/320", "", "1d8", "+1", "5", "default_enemy");

# Bear
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("6", "Brown Bear", "11", "34", "40/30(climbing)", "19", "10", "16", "2", "13", "7", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Bite", "+5", "5", "", "1d8", "+4", "6", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Claws", "+5", "5", "", "2d6", "+4", "6", "default_enemy");

# Kobold
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("7", "Kobold", "12", "5", "30", "7", "15", "9", "8", "7", "8", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Dagger", "+4", "5", "", "1d4", "+2", "7", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Sling", "+4", "30/120", "", "1d4", "+2", "7", "default_enemy");

# Ghoul
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("8", "Ghoul", "12", "22", "30", "13", "15", "10", "7", "10", "6", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Bite", "+2", "5", "", "2d6", "+2", "8", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Claws", "+4", "5", "", "2d4", "+2", "8", "default_enemy");

# Orc
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("9", "Orc", "13", "15", "30", "16", "12", "16", "7", "11", "10", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Greataxe", "+5", "5", "", "1d12", "+3", "9", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Javelin", "+4", "30/120", "", "1d6", "+3", "9", "default_enemy");

# Mage
INSERT INTO enemies(enemy_id, enemy_name, enemy_ac, enemy_hp, enemy_speed, strength, dexterity, constitution, intelligence, wisdom, charisma, username)
values("10", "Mage", "12/15(Mage Armour)", "40", "30", "9", "14", "11", "17", "12", "11", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Firebolt", "+5", "120", "", "1d10", "", "10", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Magic Missile", "100%", "120", "", "3d4", "+3", "10", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Fireball", "Dex 15", "150", "20 Sphere", "8d6", "", "10", "default_enemy");
INSERT INTO actions(action_name, hit_chance, reach, area, damage, modifier, enemy_id, username)
values("Cone Of Cold", "Con 15", "Self", "60 Cone", "8d8", "", "10", "default_enemy");


