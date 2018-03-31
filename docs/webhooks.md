# About webhooks

A [webhook](https://en.wikipedia.org/wiki/Webhook) is a mechanism for notifying
over HTTP a third-party server when an event occurs. For instance, if a webhook
is configured to listen on 'new comment' events with the URL
`http://example.com/`, Postmill will send a POST request to that URL every time
a new comment is posted.

Currently, webhooks are added on a per-forum basis, and are managed by forum
moderators. The ability to add global webhooks is planned.

For security reasons, webhooks are disabled by default. If you trust your mods,
or you understand the security implications of letting your server make an
arbitrary number of HTTP requests to arbitrary servers when an event occurs, you
can set the `APP_ENABLE_WEBHOOKS` environment variable to `1` to enable
webhooks.

Performance
---

Webhooks are dispatched when Symfony's `kernel.terminate` event has been
dispatched. In practice, this means that setups which don't use PHP-FPM will
dispatch the webhooks before delivering the response, which can make events
seem slow to users.

Secret token
---

The secret token, if specified, will be included in the outgoing request via the
`X-Postmill-Secret` header.

Request bodies
---

### New submission

~~~json
{
    "event": "new_submission",
    "subject": {
        "resource": "https://example.com/f/example/420/smoke-weed",
        "id": 420,
        "forum": "https://example.com/f/example",
        "user": "https://example.com/user/emma",
        "title": "Smoke weed!",
        "body": "its good for your soul",
        "url": "https://foo.example.com/",
        "timestamp": "2018-04-20T06:09:00+00:00",
        "locked": false,
        "sticky": false,
        "user_flag": "moderator",
        "edited_at": "2018-06-09T04:21:09+00:00",
        "moderated": false,
        "comment_count": 0,
        "upvotes": 0,
        "downvotes": 0,
        "thumbnail_1x": "https://example.com/very_long_image_url.jpg",
        "thumbnail_2x": "https://example.com/very_long_image_url_2x.jpg"
    }
}
~~~

Note that thumbnails are very unlikely to be available before the webhook has
been dispatched.

### Edit submission

~~~json
{
    "event": "edit_submission",
    "subject": {
        "before": {
            "resource": "https://example.com/f/example/420/smoke-weed",
            "id": 420,
            "forum": "https://example.com/f/example",
            "user": "https://example.com/user/emma",
            "title": "Smoke weed!",
            "body": "its good for your soul",
            "url": "https://foo.example.com/",
            "timestamp": "2018-04-20T06:09:00+00:00",
            "locked": false,
            "sticky": false,
            "user_flag": "moderator",
            "moderated": false,
            "comment_count": 69,
            "upvotes": 420,
            "downvotes": 69,
            "thumbnail_1x": "https://example.com/very_long_image_url.jpg",
            "thumbnail_2x": "https://example.com/very_long_image_url_2x.jpg"
        },
        "after": {
            "resource": "https://example.com/f/example/420/smoke-weed",
            "id": 420,
            "forum": "https://example.com/f/example",
            "user": "https://example.com/user/emma",
            "title": "Smoke weed!",
            "body": "actually don't",
            "url": "https://foo.example.com/",
            "timestamp": "2018-04-20T06:09:00+00:00",
            "locked": false,
            "sticky": false,
            "user_flag": "moderator",
            "edited_at": "2018-06-09T04:21:09+00:00",
            "moderated": false,
            "comment_count": 69,
            "upvotes": 420,
            "downvotes": 69,
            "thumbnail_1x": "https://example.com/very_long_image_url.jpg",
            "thumbnail_2x": "https://example.com/very_long_image_url_2x.jpg"
        }
    }
}
~~~

### New comment

~~~json
{
    "event": "new_comment",
    "subject": {
        "resource": "https://example.com/f/example/420/comment/1312",
        "id": 1312,
        "body": "Raw body of comment",
        "timestamp": "2018-04-20T06:09:00+00:00",
        "user": "https://example.com/user/emma",
        "submission": "https://example.com/f/example/420/smoke-weed",
        "parent": "https://example.com/f/example/420/comment/69",
        "reply_count": 0,
        "upvotes": 0,
        "downvotes": 0,
        "soft_deleted": false,
        "edited_at": "2018-06-09T04:21:09+00:00",
        "moderated": false,
        "user_flag": "admin"
    }
}
~~~

### Edit comment

~~~json
{
    "event": "edit_comment",
    "subject": {
        "before": {
            "resource": "https://example.com/f/example/420/comment/1312",
            "id": 1312,
            "body": "Smoke a little weed",
            "timestamp": "2018-04-20T06:09:00+00:00",
            "user": "https://example.com/user/emma",
            "submission": "https://example.com/f/example/420/smoke-weed",
            "parent": "https://example.com/f/example/420/comment/69",
            "reply_count": 42069,
            "upvotes": 420,
            "downvotes": 69,
            "soft_deleted": false,
            "moderated": false,
            "user_flag": "admin"
        },
        "after": {
            "resource": "https://example.com/f/example/420/comment/1312",
            "id": 1312,
            "body": "Smoke lots of weed",
            "timestamp": "2018-04-20T06:09:00+00:00",
            "user": "https://example.com/user/emma",
            "submission": "https://example.com/f/example/420/smoke-weed",
            "parent": "https://example.com/f/example/420/comment/69",
            "reply_count": 42069,
            "upvotes": 420,
            "downvotes": 69,
            "soft_deleted": false,
            "edited_at": "2018-06-09T04:21:09+00:00",
            "moderated": false,
            "user_flag": "admin"
        }
    }
}
~~~
