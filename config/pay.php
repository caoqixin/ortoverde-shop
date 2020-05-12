<?php

return [
    'alipay' => [
        'app_id'         => '2016102200741342',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgDbxe1QB43ZEN4BKsmikgMn98tHZc+dMbWr0DuYdIPz9utQqtNzIlFKWOS1yqc/kAU1z0f3W8pWPvKU61cuYzTo8cOx31uQ8otRJ+MLczN/NKiHvUTVsETL0gNvHhpabR/tMlFrPboEJJpErBjQhHfY2QdYvvKdfWj3SuwsVrW+ttpVFUIfd9yzxpd5RQP1lkMTrRS1UusvEDNYyj9gqwSNmMIDQlppaUiAy7jVFCPnjwQUWYJ7t1MUXWYtIq8uKpLAy6eayJhPDIQLN3HIW1q2lXvVki9zPSzVDxzSZIgFLUwFqzmHZI8waizRMIJn9SeDyoIMAAzTEC/Xp4b5V/QIDAQAB',
        'private_key'    => 'MIIEpQIBAAKCAQEAvA6G92MvtFUnla9uYX+jMEOK2y6YVOAgTzElAVMSPGT4sA7w+3EA0maoeT8zBF6KNdbkd3YeXWuwYcIe5xBElCXFsUElZJ9OThzxf3Qh7JBh1qa3pV8ZC32zWj+SqKbcbMWZ6NvKL1pRAgE8NYex2biFVZ2rkBoskGuaoCGl7gnoAYtH/Cc8wHebYlSFjx2fgTXL0NOnhc8i8kGBJ0s8Cuci0LrCI+tR8ghsF/++ZIm6H4kHs22RttLHBzuAr/T6uvtVOax0wXmtGJLh2QF1ReuwyqZ5qSLEcik/rIC39+H2hf+37xbitB8qxaIwx8uZo4E3bhAvxjfufppaUwu+NQIDAQABAoIBAQCaIA3dkxz13xSiSTVeEw3b8H9NQ9L1PPs97Dk37K11cgufv58hdGwIBLrvJCzb19/OYGC+x7/7MNdOR5qsJVkPUiXEMHBFF4tF2dLTcoqbJ3oIQnsgvd/42vyzaob4Ukk1nH2Xqwr55DugOQLvnFbW6bPdh2ukns8HTilcNDeZ7nrvU2veKjAcLzA5D2fa+ETpwDswXDdrcLHlLObyXrCa0q/Q2MWFKMeZhFYNu7f3sN6AAs+EWJNHrcR6tb5jHvMh3NXYjMhaH3q4mvD5ZZifo/ePa3IbaXNnMU9BbYN2zsfNyCr6pqLXpA6A5XQ+Q3Sywte1vKOqWEnPzzQawDnBAoGBAPNpBL0QutP7x4zRGq+Qrsa1l5takfAmlTgzdY2Fm1TtGz3ZAisgqRYEAdrLVpICT6oBWYPYh+CuCpNb1dp9cPSzi2Dkz+jHz4i5YIAayUSjI4xc9KeE+pjF1BulGNgIYWOR8l2lHeSGAnnrIFi0tzuHPXnb/5H3UKc1qGeSPBmHAoGBAMXIk4eOgv1xMBy2ShMakmJHqoUHpYBt58yYXjvtnSEHDkPRBFxNsfflbYTIfw0tfXIuprWeKCcU024ekzVeYIiUcIcagtzGw0jI8MD4JQoZr2Zzx+220sRDdGBCmQnH6dFF4LMd4zHkQspz66qbUSGvGkg+dvyj/Tiazch1XuljAoGBAIvB57uaqHrgnMHoqfbWDtP/He5QQWzu6kybl0pLxVUs0rBfMlSK6yq94Ea865boBs+o+LmwEMxbgaz8VLyfu8R0dnmKxylz2GA7eAH130wuk1Gbacnpm20uYUwCLlKT9T8TZGKKVLCKlU9lRmxITtC6JP0b6RDpPIDVWT0mhKVvAoGANcgl/d2N1xcZSyVynSFDnv/36Xa71WGNf8ALF+a0LI/7nAtRUTw7Ybp8fnf6vH2bOBphcM+SAZaTK8WaqA+C1oDu3H4kCZ7u6XLirMaNY+K6JHTlb8mgJIhnM+nILbWz6hlDUdGVvzJfyyecdOcJN8yiq/R4bJi3OdY2kQUBixsCgYEAwqT4wX1pfCi2rtu0NuOnMylX3tTI9h24ag1gB6G1iroHNYphe1+Z07XZ//I/tKzhhv80SZdGTaVcL2lbNQKhjG3uUClZDaCFF/BuptQqlVuK9ciL1nX60x/tBOHj5LzF0DoFd7fIob33RdN6lDy4rTAME76XD0iPel1XwZx2+QQ=',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];