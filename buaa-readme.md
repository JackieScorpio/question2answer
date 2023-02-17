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
