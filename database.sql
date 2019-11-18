CREATE DATABASE IF NOT EXISTS api_rest_laravel;
USE api_rest_laravel;

CREATE TABLE users(
    id int(255) auto_increment not null ,
    name varchar(50) not null ,
    surname varchar(100),
    role varchar(20),
    email varchar(255) not null ,
    password varchar(255) not null ,
    created_at datetime DEFAULT NULL,
    update_at datetime DEFAULT NULL,
    remember_token varchar(255),
    CONSTRAINT pk_users PRIMARY KEY (id)
)ENGINE=InnoDb;

CREATE TABLE categories(
    id int(255) auto_increment not null ,
    name varchar(155) not null ,
    created_at datetime DEFAULT NULL,
    update_at datetime DEFAULT NULL,
    CONSTRAINT pk_categories PRIMARY KEY (id)
)ENGINE=InnoDb;

CREATE TABLE posts(
   id int(255) auto_increment not null ,
   user_id int(255) not null,
   category_id int(255) not null,
   title varchar(155) not null ,
   content text not null,
   image varchar(155),
   created_at datetime DEFAULT NULL,
   update_at datetime DEFAULT NULL,
   CONSTRAINT pk_posts PRIMARY KEY (id),
   CONSTRAINT fk_post_user FOREIGN KEY(user_id) references users(id),
   CONSTRAINT fk_post_category FOREIGN KEY(category_id) references categories(id)
)ENGINE=InnoDb;
