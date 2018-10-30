class UserAddController {
  constructor ($stateParams, $state, API, AclService) {
    'ngInject'

    this.$state = $state
    this.formSubmitted = false
    this.API = API
    this.alerts = []

    this.can = AclService.can
    
    if ($stateParams.alerts) {
      this.alerts.push($stateParams.alerts)
    }

    let Roles = API.service('roles', API.all('users'))
    Roles.getList()
      .then((response) => {
        let systemRoles = []
        let roleResponse = response.plain()

        angular.forEach(roleResponse, function (value) {
          systemRoles.push({id: value.id, name: value.name})
        })

        this.systemRoles = systemRoles
      })
  }

  save (isValid) {
    this.$state.go(this.$state.current, {}, { alerts: 'test' })

    if (isValid) {
      let Users = this.API.service('users')
      let $state = this.$state
      
      Users.post({
        'name': this.name,
        'email': this.email,
        'email_verified': this.email_verified,
        'password': this.password,
        'roles': this.roles
      }).then(function () {
        let alert = { type: 'success', 'title': 'Success!', msg: 'User has been added.' }
        $state.go($state.current, { alerts: alert})
      }, function (response) {
        let alert = { type: 'error', 'title': 'Error!', msg: response.data.message }
        $state.go($state.current, { alerts: alert})
      })
    } else {
      this.formSubmitted = true
    }
  }

  $onInit () {}
}

export const UserAddComponent = {
  templateUrl: './views/app/components/user-add/user-add.component.html',
  controller: UserAddController,
  controllerAs: 'vm',
  bindings: {}
}
