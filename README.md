**Still under development and not functional!**

# ISC DHCP Web API

>This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
>
>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
>
>You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

## API end points
### Hosts

API endpoint                  | HTTP Method          | Description
----------------------------- | -------------------- | -----------
`/endhosts/all`               | `GET`                | Get all endhosts
`/endhosts/id/{$id}`          | `GET` `PUT` `DELETE` | Get, update or delete endhost with ID `$id`
`/endhosts/mac/{$mac}`        | `GET`                | Get endhost with MAC address `$mac`
`/endhosts/search/{$pattern}` | `GET`                | Get endhost where `$pattern` matches:<br/> hostname, MAC address or description
`/endhosts/new`               | `POST`               | Create new endhost

### Host types

API endpoint               | HTTP Method         | Description
-------------------------- | ------------------- | ----
`/endhosts/types/all`      | `GET`               | Get all endhost types
`/endhosts/types/id/{$id}` | `GET` `PUT` `DELETE`| Get, update or delete endhost type with ID `$id`
`/endhosts/types/new`      | `POST`              | Create new endhost type

### Reservations

API endpoint                  | HTTP Method          | Description
----------------------------- | -------------------- | -----------
`/reservations`               | `GET`                | Get all reservations
`/reservations/id/{$id}`      | `GET` `PUT` `DELETE` | Get, update or delete reservation with ID `$id`
`/reservations/ip/{$ip}`      | `GET`                | Get reservation for IP address `$ip`
`/reservations/mac/{$mac}`    | `GET`                | Get reservation for MAC address `$mac`
`/reservations/subnet/{$id}`  | `GET`                | Get all reservations in a subnet with ID `$id`
`/reservations/group/{$id}`   | `GET`                | Get all reservations in a group with ID `$id`
`/reservations/new`           | `POST`               | Create new reservation

### Subnets

API endpoint               | HTTP Method         | Description
-------------------------- | ------------------- | ----
`/subnets/all`             | `GET`               | Get all subnets |
`/subnets/id/{$id}`        | `GET` `PUT` `DELETE`| Get, update or delete subnet with ID `$id` |
`/subnets/id/{$id}/free`   | `GET`               | Get all free IP addresses in subnet with ID `$id` |
`/subnets/ip/{$ip}`        | `GET`               | Get subnet for IP address `$ip` |
`/subnets/vlan/{$vlanid}`  | `GET`               | Get subnet for VLAN ID `$vlanid` |
`/subnets/new`             | `POST`              | Create new subnet |

### Groups

API endpoint              | HTTP Method         | Description
------------------------- | ------------------- | ----
`/groups/all`             | `GET`               | Get all groups
`/groups/subnet/{$id}`    | `GET`               | Get all groups that belong to subnet with ID `$id`
`/groups/id/{$id}`        | `GET` `PUT` `DELETE`| Get, update or delete group  with ID `$id`
`/groups/new`             | `POST`              | Create new group
