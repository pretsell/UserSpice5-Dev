RENAME TABLE
    permissions TO groups,
    user_permission_matches TO groups_users_raw,
    permission_page_matches TO groups_pages;

ALTER TABLE `groups_pages` CHANGE `permission_id` `group_id` INT(15) NOT NULL;

ALTER TABLE `groups_users_raw`
    CHANGE `permission_id` `group_id` INT(11) NOT NULL,
    ADD `user_is_group` BOOLEAN NOT NULL DEFAULT FALSE AFTER `group_id`;

-- VIEW: groups_users
-- This view flattens all nested groups to show which users are members of which
-- groups. When seeking group members, this is the view to use exclusively.
CREATE OR REPLACE VIEW groups_users AS
    SELECT id, user_id, group_id, 0 AS nested
    FROM groups_users_raw
    WHERE user_is_group = 0
    UNION
    SELECT ug1.user_id+ug2.group_id*10000 AS id, ug1.user_id, ug2.group_id, 1 AS nested
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    WHERE ug2.user_is_group = 1
    AND ug1.user_is_group = 0
    UNION
    SELECT ug1.user_id+ug3.group_id*10000 AS id, ug1.user_id, ug3.group_id, 1 AS nested
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
    WHERE ug3.user_is_group = 1
    AND ug2.user_is_group = 1
    AND ug1.user_is_group = 0
    UNION
    SELECT ug1.user_id+ug4.group_id*10000 AS id, ug1.user_id, ug4.group_id, 1 AS nested
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
    JOIN groups_users_raw ug4 ON (ug3.group_id = ug4.user_id)
    WHERE ug4.user_is_group = 1
    AND ug3.user_is_group = 1
    AND ug2.user_is_group = 1
    AND ug1.user_is_group = 0
    UNION
    SELECT ug1.user_id+ug5.group_id*10000 AS id, ug1.user_id, ug5.group_id, 1 AS nested
    FROM groups_users_raw ug1
    JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
    JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
    JOIN groups_users_raw ug4 ON (ug3.group_id = ug4.user_id)
    JOIN groups_users_raw ug5 ON (ug4.group_id = ug5.user_id)
    WHERE ug5.user_is_group = 1
    AND ug4.user_is_group = 1
    AND ug3.user_is_group = 1
    AND ug2.user_is_group = 1
    AND ug1.user_is_group = 0;

    -- VIEW: groups_groups
    -- This view flattens ALL groups into a parent_id/child_id relationship. Note
    -- that even the special case where parent_id==child_id is represented here so
    -- you can always safely use this view
    CREATE OR REPLACE VIEW groups_groups AS
        SELECT id*10000+id, id AS parent_id, id AS child_id
        FROM groups
        UNION
        SELECT ug1.group_id*10000+ug1.user_id AS id, ug1.group_id AS parent_id, ug1.user_id AS child_id
        FROM groups_users_raw ug1
        WHERE ug1.user_is_group = 1
        UNION
        SELECT ug1.group_id*10000+ug2.user_id AS id, ug1.group_id AS parent_id, ug2.user_id AS child_id
        FROM groups_users_raw ug1
        JOIN groups_users_raw ug2 ON (ug1.user_id = ug2.group_id)
        WHERE ug2.user_is_group = 1
        AND ug1.user_is_group = 1
        UNION
        SELECT ug1.group_id*10000+ug3.user_id AS id, ug1.group_id, ug3.user_id
        FROM groups_users_raw ug1
        JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
        JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
        WHERE ug3.user_is_group = 1
        AND ug2.user_is_group = 1
        AND ug1.user_is_group = 1
        UNION
        SELECT ug1.group_id*10000+ug4.user_id AS id, ug1.group_id, ug4.user_id
        FROM groups_users_raw ug1
        JOIN groups_users_raw ug2 ON (ug1.group_id = ug2.user_id)
        JOIN groups_users_raw ug3 ON (ug2.group_id = ug3.user_id)
        JOIN groups_users_raw ug4 ON (ug3.group_id = ug4.user_id)
        WHERE ug4.user_is_group = 1
        AND ug3.user_is_group = 1
        AND ug2.user_is_group = 1
        AND ug1.user_is_group = 1;

-- this has nothing to do with groups, but is a way to update menus table to new page_id values
ALTER TABLE `menus`
    ADD `page_id` BOOLEAN NULL AFTER `link`;
UPDATE `menus` SET `page_id`=(select id from pages where pages.page = menus.link)
