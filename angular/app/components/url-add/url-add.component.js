class UrlAddController {
  constructor (API, $state, $filter, $stateParams, AclService) {
    'ngInject'

    this.$state = $state
    this.formSubmitted = false
    this.API = API
    this.filter = $filter
    this.alerts = []

    this.hasRole = AclService.hasRole

    if ($stateParams.alerts) {
      this.alerts.push($stateParams.alerts)
    }

    if(this.hasRole('role.admin')) {
      let Consumers = API.service('consumers', API.all('users'))
      Consumers.getList()
        .then((response) => {
          let consumers = []
          let consumerResponse = response.plain()

          angular.forEach(consumerResponse, function (value) {
            consumers.push({id: value.id, name: value.name})
          })

          this.consumers = consumers
        })
    }

  }

  save (isValid) {
    this.$state.go(this.$state.current, {}, { alerts: 'test' })
    if (isValid) {
      let Urls = this.API.service('urls', this.API.all('users'))
      let $state = this.$state

      Urls.post({
        'user_id': this.consumer,
        'url': this.url
      }).then(function () {
        let alert = { type: 'success', 'title': 'Success!', msg: 'A url has been shortened.' }
        $state.go($state.current, { alerts: alert})
      }, function (response) {
        let alert = { type: 'error', 'title': 'Error!', msg: 'This is an invalid url or already shortened.'}
        $state.go($state.current, { alerts: alert})
      })
    } else {
      this.formSubmitted = true
    }
  }

  $onInit () {}
}

export const UrlAddComponent = {
  templateUrl: './views/app/components/url-add/url-add.component.html',
  controller: UrlAddController,
  controllerAs: 'vm',
  bindings: {}
}
