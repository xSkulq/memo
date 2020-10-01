サービス名 memo

テーブル設計

userテーブル

アカウント情報  

users

| 名前                              | カラム名          | データ型 | 備考                       |
--------------------------------------------------------------------------------------------
| ID（PK）                          | id              | Integer  | PRIMAY、NOT NULL          |
| email                            | email           | string   | NOT NULL UNIQUE           |
| passowrd                         | password        | string   | NOT NULL                  |
| delete                           | is_deleted       | bloolean | NOT NULL                  |
| 作成日時                           | created_at        | Datetime |                            |
| 更新日時                           | updated_at        | Datetime |                            |


メモ情報

memo

| 名前                              | カラム名          | データ型 | 備考                       |
--------------------------------------------------------------------------------------------
| ID(PK)                           | id               | Integer | PRIMAY、NOT NULL          |
| 気になったこと                     | concern          | string  | NOT NULL                  |
| 思ったこと                        | thought          | string  | NOT NULL                  |
| ユーザーid                         | user_id           | integer  |                        |
| delete                           | is_deleted       | bloolen | NOT NULL                  |
| 作成日時                           | created_at        | Datetime |                        |
| 更新日時                           | updated_at        | Datetime |                        |

エラーメッセージ
MSG01  入力してください
MSG02  Eメールの形式で入力してください
MSG03  6文字以上で入力してください
MSG04  255文字以内で入力してください
MSG05  入力されたパスワードの文字と違います