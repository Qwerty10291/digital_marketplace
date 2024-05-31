DROP DATABASE shop;
CREATE DATABASE shop;
USE shop;


CREATE PROCEDURE UpdateCategoryCounter(IN category_id BIGINT)
BEGIN
    DECLARE parent_id BIGINT;

    -- Обновление текущей категории
    UPDATE categories
    SET products_count = products_count + 1
    WHERE id = category_id;

    -- Получение parent_id текущей категории
    SELECT parent_id INTO parent_id
    FROM categories
    WHERE id = category_id;

    -- Рекурсивное обновление родительской категории, если таковая существует
    IF parent_id IS NOT NULL THEN
        CALL UpdateCategoryCounter(parent_id);
    END IF;
END;


CREATE OR REPLACE TRIGGER update_category_counter AFTER INSERT ON products FOR EACH ROW BEGIN
    CALL UpdateCategoryCounter(NEW.category_id);
end;

DROP TRIGGER update_product_rating;
CREATE TRIGGER update_product_rating AFTER INSERT ON reviews FOR EACH ROW BEGIN
    UPDATE products set rating_count =  rating_count + 1, rating_sum = rating_sum + NEW.rating, user_id = @sellerId := user_id WHERE products.id = NEW.product_id;
    UPDATE users SET rating = (select sum(products.rating_sum) / sum(products.rating_count) from products where products.user_id = @sellerId) where id = @sellerId;
end;

DROP TRIGGER recalc_product_rating;
CREATE TRIGGER recalc_product_rating AFTER UPDATE ON reviews FOR EACH ROW BEGIN
    UPDATE products SET rating_sum  = rating_sum - OLD.rating + NEW.rating, rating_count = rating_count, user_id = @sellerId := user_id WHERE products.id = NEW.product_id;
    UPDATE users SET rating = (select sum(products.rating_sum) / sum(products.rating_count) from products where products.user_id = @sellerId) where id = @sellerId;
end;

DROP TRIGGER close_order;
CREATE TRIGGER close_order AFTER UPDATE ON orders FOR EACH ROW BEGIN
    IF  NEW.status = 'completed' THEN
         UPDATE users SET balance = balance + NEW.price, orders_count = orders_count + 1 WHERE id = NEW.seller_id;
    end if;
end;

CREATE TRIGGER check_withdraw_transaction BEFORE INSERT ON transactions FOR EACH ROW BEGIN
    DECLARE userBalance decimal(8,2);

    IF NEW.type = 'withdraw' then
        SELECT balance into userBalance from users where id = NEW.user_id;
        IF NEW.amount > userBalance then
            SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'У вас недостаточно средств';
        end if;
    end if;
end;

CREATE TRIGGER check_user_balance BEFORE INSERT ON orders FOR EACH ROW BEGIN
    DECLARE userBalance decimal(8,2);
    DECLARE productPrice decimal(8, 2);
    SELECT price into productPrice from products where id = NEW.product_id;
    SELECT balance into userBalance from users where id = NEW.customer_id;
    IF userBalance < productPrice then
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'У вас не хватает средств чтобы купить данный товар';
    end if;
end;

CREATE PROCEDURE create_comment(IN userId int, IN productId INT, IN comm TEXT, IN reviewRating INT) BEGIN
    DECLARE orders_count INT;
    DECLARE comm_count INT;
    START TRANSACTION;
    SELECT count(*) INTO orders_count FROM orders WHERE
    orders.customer_id = userId and orders.product_id = productId and orders.status = 'completed';

    SELECT count(*) INTO comm_count FROM reviews WHERE reviews.user_id = userId and reviews.product_id = productId;
    IF comm_count >= orders_count THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'Вы не можете создать комментарий не купив товар';
    end if;
    INSERT INTO reviews (product_id, user_id, comment, rating, created_at, updated_at) VALUES (productId, userId, comm, reviewRating, now(), now());
    COMMIT;
end;


CREATE PROCEDURE create_order(IN userId INT, IN productId INT) BEGIN
    DECLARE sellerId BIGINT;
    DECLARE productPrice DECIMAL(8, 2);
    START TRANSACTION;
    SELECT products.user_id, products.price FROM products where products.id = productId INTO sellerId, productPrice;
    INSERT INTO orders (product_id, seller_id, customer_id, price, created_at, updated_at)
        VALUES (productId, sellerId, userId, productPrice, now(), now());
    UPDATE users SET balance = balance - productPrice where users.id = userId;
    COMMIT;
end;

DROP PROCEDURE  withdraw;
CREATE PROCEDURE withdraw(IN userId int, in summ float, in paymentSystem text) BEGIN
    DECLARE user_balance DECIMAL(8, 2);
    START TRANSACTION;
    SELECT balance into user_balance from users where users.id = userId;

    IF user_balance < summ THEN
        ROLLBACK;
        SIGNAL SQLSTATE  '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'У вас недостаточно средств';
    end if;
    UPDATE users SET balance = balance - summ WHERE users.id = userId;
    INSERT INTO transactions (user_id, amount, payment_system, status, type) VALUES (userId, summ, paymentSystem, 'created', 'withdraw');
    COMMIT;
end;


CREATE OR REPLACE PROCEDURE GetSubcategories(IN category_id BIGINT)
BEGIN
    WITH RECURSIVE subcategories AS (
        SELECT
            id,
            parent_id,
            name,
            products_count,
            created_at,
            updated_at
        FROM
            categories
        WHERE
            id = category_id

        UNION ALL

        SELECT
            c.id,
            c.parent_id,
            c.name,
            c.products_count,
            c.created_at,
            c.updated_at
        FROM
            categories c
        INNER JOIN
            subcategories sc ON sc.id = c.parent_id
    )
    SELECT
        id
    FROM
        subcategories;
END;

CALL GetSubcategories(1);

INSERT INTO users (name, email, password,  created_at, updated_at, role, balance, orders_count, rating) VALUES
('customer', 'customer@mail.ru', '', now(), now(), 'user', 1000, 0, 0),
('seller', 'seller@mail.ru', '', now(), now(), 'seller', 2000, 0, 0);

TRUNCATE TABLE users;
select  * from users;

INSERT INTO categories (name, parent_id, products_count, created_at, updated_at) VALUES
('игра', null,  0, now(), now()),
('программа', null, 0, now(), now()),
('ключи для игр(steam)', 1, 0, now(), now()),
('аккаунт steam', 1, 0, now(), now()),
('ключ лицензии', 2, 0, now(), now());

TRUNCATE TABLE categories;
SELECT * from categories;

INSERT INTO products (user_id, category_id, name, description, price, deleted_at, created_at, updated_at) VALUES
(4, 3, 'Sekiro shadows die twice ключ steam', '', 500, null, now(), now()),
(4, 4, 'Аккаунт Counter Strike 2', '', 200, null, now(), now()),
(4, 5, 'Ключ на лицензию Goland IDE', '', 1000, null, now(), now());

DELETE  FROM products;

SELECT * from products;

CALL create_order(1, 1);
select * from orders;
UPDATE orders SET status = 'completed' where id = 1;
CALL create_comment(1, 1, 'топ товар', 5);
select * from reviews;

call withdraw(2, 2000, 'payment');

TRUNCATE orders;
TRUNCATE reviews;
UPDATE users SET balance = balance + 3000;

alter table products add column category_id bigint after user_id;
alter table categories add column parent_id bigint after id;