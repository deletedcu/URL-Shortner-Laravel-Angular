class UrlEditController {
  constructor (API, $state, $filter, $stateParams) {
    'ngInject'

    this.$state = $state
    this.formSubmitted = false
    this.API = API
    this.alerts = []
    this.filter = $filter

    if ($stateParams.alerts) {
      this.alerts.push($stateParams.alerts)
    }

    let urlId = $stateParams.urlId
    let UrlData = API.service('show', API.all('urls'))
    UrlData.one(urlId).get()
      .then((response) => {
        this.urleditdata = API.copy(response)
        console.log(this.urleditdata)
      })
  }

  save (isValid) {
    this.$state.go(this.$state.current, {}, { alerts: 'test' })
    if (isValid) {
      let $state = this.$state

      this.urleditdata.put()
        .then(() => {
          let alert = { type: 'success', 'title': 'Success!', msg: 'Url has been updated.' }
          $state.go($state.current, { alerts: alert})
        }, (response) => {
          let alert = { type: 'error', 'title': 'Error!', msg: response.data.message }
          $state.go($state.current, { alerts: alert})
        })
    } else {
      this.formSubmitted = true
    }
  }

  $onInit () {}
}

export const UrlEditComponent = {
  templateUrl: './views/app/components/url-edit/url-edit.component.html',
  controller: UrlEditController,
  controllerAs: 'vm',
  bindings: {}
}
