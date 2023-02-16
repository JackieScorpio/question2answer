## Superadmin：
    - username: buaasuperadmin
    - password: complexpassword123
    - email: 415269619@qq.com

## Goal
1. Fa：记录一个回答是否为该问题的首次回答。 yes  qa_posts 根据parentid判断回答的所属问题，根据created来判断哪个时间最早，就是首次回答
2. Sa：回答被采纳次数。 yes qa_userpoints aselecteds
3. Va：回答的平均得票数，即：总票数/回答次数。  yes qa_posts qa_userpoints
4. Pa：回答的次数。 yes qa_userpoints aposts字段
5. Pq：提问的次数。 yes qa_userpoints qposts字段
6. Vq：问题平均得票数。yes qa_posts qa_userpoints
7. Cq：问题在列表中被点击进入的次数。  yes qa_posts clicktimes字段
    - 修改位置： qa-theme.php line 352
8. Vp：投票的次数  yes qa_userpoints
9. Dv：登录网站的天数 yes 在 qu_users 里面增加字段 logindays
10. Od：登录论坛并保持在线状态的总时长 貌似没有 看看有没有token过期，加上登出操作，算出时长，在 qu_users 增加字段 totaltime
11. Pc：对问题或回答进行评论的次数。 yes qa_userpoints cposts字段

## Need
问题被点击数  implemented
论坛访问总天数 implemented
活跃在线总时长 

## 数据库表说明
- qa-inclued/db/install.php

## 改表语句
- 表 qa_posts 增加字段 clicktimes，点击问题的时候加一
    - ALTER TABLE q2a.qa_posts ADD clicktimes BIGINT DEFAULT 0 NOT NULL;

- 表 qa_users 增加字段 loggeddays，表示用户登录的天数
    - ALTER TABLE q2a.qa_users ADD logindays BIGINT DEFAULT 1 NOT NULL;
