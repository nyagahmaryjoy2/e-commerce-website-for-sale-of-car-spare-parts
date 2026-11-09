-- Import this file into shop_db after the normal schema to add repeatable demo users and messages.
-- All demo accounts use the password: DemoPass123

INSERT INTO `users` (`name`, `email`, `password`)
SELECT 'Demo Customer', 'customer1@speedo.example', SHA1('DemoPass123')
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'customer1@speedo.example');

INSERT INTO `users` (`name`, `email`, `password`)
SELECT 'Sample Shopper', 'customer2@speedo.example', SHA1('DemoPass123')
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'customer2@speedo.example');

INSERT INTO `users` (`name`, `email`, `password`)
SELECT 'Parts Tester', 'customer3@speedo.example', SHA1('DemoPass123')
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'customer3@speedo.example');

INSERT INTO `messages` (`user_id`, `name`, `email`, `number`, `message`)
SELECT `users`.`id`, 'Demo Customer', 'customer1@speedo.example', '0000000000', 'Demo inquiry: is a front brake pad set available?'
FROM `users`
WHERE `users`.`email` = 'customer1@speedo.example'
AND NOT EXISTS (SELECT 1 FROM `messages` WHERE `email` = 'customer1@speedo.example' AND `message` = 'Demo inquiry: is a front brake pad set available?');

INSERT INTO `messages` (`user_id`, `name`, `email`, `number`, `message`)
SELECT `users`.`id`, 'Sample Shopper', 'customer2@speedo.example', '0000000000', 'Demo inquiry: what is the delivery time for an order?'
FROM `users`
WHERE `users`.`email` = 'customer2@speedo.example'
AND NOT EXISTS (SELECT 1 FROM `messages` WHERE `email` = 'customer2@speedo.example' AND `message` = 'Demo inquiry: what is the delivery time for an order?');

INSERT INTO `messages` (`user_id`, `name`, `email`, `number`, `message`)
SELECT `users`.`id`, 'Parts Tester', 'customer3@speedo.example', '0000000000', 'Demo inquiry: can I confirm stock before checkout?'
FROM `users`
WHERE `users`.`email` = 'customer3@speedo.example'
AND NOT EXISTS (SELECT 1 FROM `messages` WHERE `email` = 'customer3@speedo.example' AND `message` = 'Demo inquiry: can I confirm stock before checkout?');
