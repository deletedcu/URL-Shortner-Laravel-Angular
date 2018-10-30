class UrlListsController {
  constructor ($scope, $state, $compile, DTOptionsBuilder, DTColumnBuilder, API, AclService) {
    'ngInject'
    this.API = API
    this.$state = $state
    this.$scope = $scope
    this.$compile = $compile
    this.DTOptionsBuilder = DTOptionsBuilder
    this.DTColumnBuilder = DTColumnBuilder

    this.hasRole = AclService.hasRole

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

    this.showUrls()
  }

  showUrls() {
    this.displayTable = false

    let Urls = this.API.service('urls', this.API.all('users'))
    if(this.consumer)
      Urls = this.API.service('urls', this.API.one('users', this.consumer))
   
    Urls.getList()
      .then((response) => {
        let dataSet = response.plain()

        this.dtOptions = this.DTOptionsBuilder.newOptions()
          .withOption('data', dataSet)
          .withOption('createdRow', createdRow)
          .withOption('responsive', true)
          .withBootstrap()

        this.dtColumns = [
          this.DTColumnBuilder.newColumn('id').withTitle('ID').withOption('width', '5%'),
          this.DTColumnBuilder.newColumn('url').withTitle('URL').withOption('width', '50%'),
          this.DTColumnBuilder.newColumn('short_route').withTitle('Short Route').withOption('width', '20%'),
          this.DTColumnBuilder.newColumn('visits').withTitle('Visits'),
          this.DTColumnBuilder.newColumn(null).withTitle('Actions').notSortable()
            .renderWith(actionsHtml)
        ]

        this.displayTable = true
      })

    let createdRow = (row) => {
      this.$compile(angular.element(row).contents())(this.$scope)
    }

    let actionsHtml = (data) => {
      return `
                <a class="btn btn-xs btn-warning" ui-sref="app.urledit({urlId: ${data.id}})">
                    <i class="fa fa-edit"></i>
                </a>
                &nbsp
                <button class="btn btn-xs btn-danger" ng-click="vm.delete(${data.id})">
                    <i class="fa fa-trash-o"></i>
                </button>`
    }
  }

  delete (urlId) {
    let API = this.API
    let $state = this.$state

    swal({
      title: 'Are you sure?',
      text: 'You will not be able to recover this data!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#DD6B55',
      confirmButtonText: 'Yes, delete it!',
      closeOnConfirm: false,
      showLoaderOnConfirm: true,
      html: false
    }, function () {
      API.one('urls', urlId).remove()
        .then(() => {
          swal({
            title: 'Deleted!',
            text: 'A url has been deleted.',
            type: 'success',
            confirmButtonText: 'OK',
            closeOnConfirm: true
          }, function () {
            $state.reload()
          })
        })
    })
  }

  $onInit () {}
}

export const UrlListsComponent = {
  templateUrl: './views/app/components/url-lists/url-lists.component.html',
  controller: UrlListsController,
  controllerAs: 'vm',
  bindings: {}
}
