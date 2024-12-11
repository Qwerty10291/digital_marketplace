create table categories
(
    id             bigint unsigned auto_increment
        primary key,
    parent_id      bigint       null,
    name           varchar(255) not null,
    products_count int          not null,
    created_at     timestamp    null,
    updated_at     timestamp    null
)

create table users
(
    id           bigint unsigned auto_increment
        primary key,
    name         varchar(255)                 not null,
    email        varchar(255)                 not null,
    password     varchar(255)                 not null,
    created_at   timestamp                    null,
    updated_at   timestamp                    null,
    role         varchar(255)  default 'user' not null,
    balance      decimal(8, 2) default 0.00   not null,
    orders_count int           default 0      not null,
    rating       double        default 0      not null,
    constraint users_email_unique
        unique (email)
)

create table products
(
    id           bigint unsigned auto_increment
        primary key,
    user_id      bigint unsigned  not null,
    category_id  bigint           null,
    name         varchar(255)     not null,
    description  varchar(255)     not null,
    price        decimal(8, 2)    not null,
    rating_count double default 0 not null,
    rating_sum   double default 0 not null,
    deleted_at   datetime         null,
    created_at   timestamp        null,
    updated_at   timestamp        null,
    constraint products_user_id_foreign
        foreign key (user_id) references users (id)
            on delete cascade
)


create table orders
(
    id          bigint unsigned auto_increment
        primary key,
    product_id  bigint unsigned            not null,
    seller_id   bigint unsigned            not null,
    customer_id bigint unsigned            not null,
    price       decimal(8, 2)              not null,
    status      varchar(255) default 'new' not null,
    created_at  timestamp                  null,
    updated_at  timestamp                  null,
    constraint orders_customer_id_foreign
        foreign key (customer_id) references users (id),
    constraint orders_product_id_foreign
        foreign key (product_id) references products (id),
    constraint orders_seller_id_foreign
        foreign key (seller_id) references users (id)
)

create table reviews
(
    id         bigint unsigned auto_increment
        primary key,
    product_id bigint unsigned  not null,
    user_id    bigint unsigned  not null,
    comment    text             not null,
    rating     double default 0 not null,
    created_at timestamp        null,
    updated_at timestamp        null,
    constraint reviews_product_id_foreign
        foreign key (product_id) references products (id),
    constraint reviews_user_id_foreign
        foreign key (user_id) references users (id)
)
    collate = utf8mb4_unicode_ci;

create table transactions
(
    id bigint unsigned auto_increment
        primary key,
    user_id        bigint unsigned not null,
    type           varchar(16)     not null,
    amount         decimal(8, 2)   not null,
    payment_system text            not null,
    status         text            not null,
    constraint transactions_user_id_foreign
        foreign key (user_id) references users (id)
)

create or replace trigger check_user_balance
    before insert
    on orders
    for each row
BEGIN
    DECLARE userBalance decimal(8,2);
    DECLARE productPrice decimal(8, 2);
    SELECT price into productPrice from products where id = NEW.product_id;
    SELECT balance into userBalance from users where id = NEW.customer_id;
    IF userBalance < productPrice then
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'У вас не хватает iwuen средств чтобы купить данный товар';
    end if;
end;

create definer = root@`%` trigger check_withdraw_transaction
    before insert
    on transactions
    for each row
BEGIN
    DECLARE userBalance decimal(8,2);

    IF NEW.type = 'withdraw' then
        SELECT balance into userBalance from users where id = NEW.user_id;
        IF NEW.amount > userBalance then
            SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = 'У вас недостаточно средств ijwebfd';
        end if;
    end if;
end;

create procedure GetSubcategories(IN category_id bigint)
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

