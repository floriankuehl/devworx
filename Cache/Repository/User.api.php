{
    "table": "user",
    "pk": "uid",
    "fieldList": [
        "uid",
        "login",
        "name",
        "salutation",
        "firstName",
        "lastName",
        "address",
        "address2",
        "zip",
        "city",
        "country",
        "email",
        "tel",
        "lastLogin",
        "cruser",
        "created",
        "updated",
        "hidden",
        "deleted"
    ],
    "typeList": "isssssssssssssissis",
    "valueList": "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?",
    "details": {
        "uid": [
            "bigint",
            20,
            "i"
        ],
        "login": [
            "varchar",
            128,
            "s"
        ],
        "name": [
            "varchar",
            32,
            "s"
        ],
        "salutation": [
            "varchar",
            10,
            "s"
        ],
        "firstName": [
            "varchar",
            32,
            "s"
        ],
        "lastName": [
            "varchar",
            32,
            "s"
        ],
        "address": [
            "varchar",
            64,
            "s"
        ],
        "address2": [
            "varchar",
            64,
            "s"
        ],
        "zip": [
            "varchar",
            6,
            "s"
        ],
        "city": [
            "varchar",
            64,
            "s"
        ],
        "country": [
            "varchar",
            2,
            "s"
        ],
        "email": [
            "varchar",
            64,
            "s"
        ],
        "tel": [
            "varchar",
            64,
            "s"
        ],
        "lastLogin": [
            "timestamp",
            0,
            "s"
        ],
        "cruser": [
            "int",
            11,
            "i"
        ],
        "created": [
            "timestamp",
            0,
            "s"
        ],
        "updated": [
            "timestamp",
            0,
            "s"
        ],
        "hidden": [
            "tinyint",
            1,
            "i"
        ],
        "deleted": [
            "timestamp",
            0,
            "s"
        ]
    },
    "defaultConditions": [
        "hidden=0",
        "ISNULL(deleted)"
    ],
    "mapToClass": ""
}