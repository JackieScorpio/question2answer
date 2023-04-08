## Superadmin：
    - username: buaasuperadmin
    - password: complexpassword123
    - email: 415269619@qq.com

## Goal
1. Fa：记录一个回答是否为该问题的首次回答。 
    - qa_posts 根据parentid判断回答的所属问题，根据created来判断哪个时间最早，就是首次回答
2. Sa：回答被采纳次数。 
    - qa_userpoints aselecteds 字段
3. Va：回答的平均得票数，即：总票数/回答次数。  
    - qa_posts qa_userpoints 字段
4. Pa：回答的次数。 
    - qa_userpoints aposts 字段
5. Pq：提问的次数。 
    - qa_userpoints qposts 字段
6. Vq：问题平均得票数。
    - qa_posts qa_userpoints 字段
7. Cq：问题在列表中被点击进入的次数。 
    - qa_posts clicktimes 字段
8. Vp：投票的次数  *votes 字段
    - qa_userpoints 
9. Dv：登录网站的天数 
    - qu_users 增加的 logindays 字段
10. Od：登录论坛并保持在线状态的总时长
    - qa_users 增加的 totalactiontime 字段

    - 用户活跃总时长算法：前提 —— 我们假设用户一次在线的操作都是比较集中且一定是有多次活跃事件出现的，所以采用如下方法。 监听ajax和page，每当有请求发送或者页面被打开都认为用户在线，将当前时间和数据库中存储的上次活跃时间 lastactiontime 进行比较，如果相差 5 分钟以内，则认为用户从上次活跃到当前时间始终在线，将两数之差加到 totalactiontime 上算出新的总在线时长。如果相差 5 分钟以上，则认为用户在上一次操作后始终处于非在线状态，则把当前时间赋给 lastactiontime，等待后续操作来计算 totalactiontime。
    - 使用登录态和token过期的方式比较难以准确估计用户的实际在线时长。
    - qa-includes/qa-ajax.php  可以对所有ajax请求作出反应
    - qa-includes/qa-page.php 进入新的页面会作出反应，几乎所有按钮点击都涉及页面的切换
11. Pc：对问题或回答进行评论的次数。 
    - qa_userpoints cposts 字段

## 数据库表字段意义说明
- qa-inclued/db/install.php

## 改表语句
- 表 qa_posts 增加字段 clicktimes，点击问题的时候加一
    - ALTER TABLE q2a.qa_posts ADD clicktimes BIGINT DEFAULT 0 NOT NULL;

- 表 qa_users 增加字段 loggeddays，表示用户登录的天数
    - ALTER TABLE q2a.qa_users ADD logindays BIGINT DEFAULT 1 NOT NULL;

- 表 qa_users 增加字段 lastactiontime， currentactiontime， totalactiontime（用户活跃总时长）
    - ALTER TABLE q2a.qa_users ADD lastactiontime varchar(255) NULL;
    - ALTER TABLE q2a.qa_users ADD totalactiontime varchar(255) NULL;

- 表 qa_users 增加字段 realname，记录用户的真实姓名
    - ALTER TABLE q2a.qa_users ADD realname varchar(255) NULL;

## qa-config.php
    unanswer.php page 无法打开 bug 修复
        - 此处设置为true: define('QA_ALLOW_UNINDEXED_QUERIES', true);

# 增加任务系统

## 建表语句
```sql
create table qa_task
(
id          int auto_increment
primary key,
started     datetime        not null,
ended       datetime        not null,
description varchar(128)    not null,
count       int default 1   not null,
reward      int default 500 null,
cat         varchar(64)     null
);

create table qa_taskfinish
(
    id      int unsigned auto_increment
        primary key,
    user_id int unsigned not null,
    task_id int          not null
);
```

## 管理员操作 - 增加eventlog

- 进入管理页面
- 点击插件
- 点击Event Logger的选项
- 点选Log events to qa_eventlog database table
- 保存

## 管理员操作 - 增加任务管理页面

- 进入管理界面
- 点击页面
- 点击添加链接
- 链接名：任务管理 
- 位置：在顶部选项卡之后 
- 可见：管理员 
- 链接URL：http://{ip}/index.php?qa=taskman

# 增加徽章系统
## 代码配置 重要！！！！
### qa-src/Controllers/User/UserPosts.php文件
代码第 498 行 categoryid 需改为 问答挑战类别 对应的id。

## 建表语句
```sql
create table qa_badge
(
    id          int auto_increment
        primary key,
    name1       varchar(128)                 not null,
    name2       varchar(128) default 'name2' not null,
    name3       varchar(128) default 'name3' not null,
    description varchar(128)                 null,
    level_1     int                          null,
    level_2     int                          null,
    level_3     int                          null
);
```
## sql语句
```sql
insert into qa_badge (id, name1, name2, name3, description, level_1, level_2, level_3)
values  (1, '灵光一闪', '乐于助人', '知无不言', '回答问题', 1, 5, 20),
        (2, '好奇宝宝', '求知达人', '探索大师', '提出问题', 1, 5, 10),
        (3, '初遇伯乐', '备受赞誉', '有口皆碑', '获得点赞', 1, 5, 10),
        (4, '吃瓜群众	', '见习评阅人', '资深评委', '参与评论', 1, 5, 10),
        (5, '学会赞美', '赞不绝口', '点赞狂魔', '进行点赞', 1, 5, 10),
        (6, '小试牛刀', '屡试不爽', '权威专家', '回答被采纳', 1, 5, 10),
        (7, '初窥门径', '驾轻就熟', '资深用户', '在线分钟数', 5, 30, 60),
        (8, '快人一步', '先行者', '急先锋', '首答次数', 1, 5, 10),
        (9, '初获关注', '热度上升', '舆论焦点', '问题被点击', 1, 5, 10),
        (10, '完美开局', '坚持不懈', '习惯养成', '登录天数', 1, 5, 10);
```



## tips
1. php version 7.4 is recommended, this can avoid bugs like categories can't be created(php version 8.0).