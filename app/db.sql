create table admin_prices (
    nid int not null,
    url varchar(1000),
    price_previous varchar(10),
    price_new varchar(10),
    selling_price varchar(10),
    created timestamp not null default
    primary key (nid)
)