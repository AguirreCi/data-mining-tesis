webpackJsonp([1],{

/***/ 277:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ResultadosDetallePageModule", function() { return ResultadosDetallePageModule; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__angular_core__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_ionic_angular__ = __webpack_require__(49);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__resultados_detalle__ = __webpack_require__(279);
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};



var ResultadosDetallePageModule = /** @class */ (function () {
    function ResultadosDetallePageModule() {
    }
    ResultadosDetallePageModule = __decorate([
        Object(__WEBPACK_IMPORTED_MODULE_0__angular_core__["I" /* NgModule */])({
            declarations: [
                __WEBPACK_IMPORTED_MODULE_2__resultados_detalle__["a" /* ResultadosDetallePage */],
            ],
            imports: [
                __WEBPACK_IMPORTED_MODULE_1_ionic_angular__["e" /* IonicPageModule */].forChild(__WEBPACK_IMPORTED_MODULE_2__resultados_detalle__["a" /* ResultadosDetallePage */]),
            ],
        })
    ], ResultadosDetallePageModule);
    return ResultadosDetallePageModule;
}());

//# sourceMappingURL=resultados-detalle.module.js.map

/***/ }),

/***/ 279:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return ResultadosDetallePage; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__angular_core__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_ionic_angular__ = __webpack_require__(49);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__providers_analisis_analisis__ = __webpack_require__(99);
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};

 //sicredisms.com

/**
 * Generated class for the ResultadosDetallePage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */
var ResultadosDetallePage = /** @class */ (function () {
    function ResultadosDetallePage(navCtrl, navParams, analisisPrv, viewCtrl, loadingCtrl) {
        this.navCtrl = navCtrl;
        this.navParams = navParams;
        this.analisisPrv = analisisPrv;
        this.viewCtrl = viewCtrl;
        this.loadingCtrl = loadingCtrl;
        this.id = '0';
        this.url = '';
        this.resultado = this.navParams.get('resultados');
        this.id = this.navParams.get('id');
        this.url = this.navParams.get('url');
    }
    ResultadosDetallePage.prototype.ionViewDidLoad = function () {
        console.log('ionViewDidLoad ResultadosDetallePage');
        this.obtenerVirusTotal();
    };
    ResultadosDetallePage.prototype.obtenerVirusTotal = function () {
        var _this = this;
        this.showLoader();
        this.analisisPrv.getVirusTotal(Number(this.id)).subscribe(function (data) {
            _this.loading.dismiss();
            console.log(data.json());
            _this.resultadoVT = data.json();
        }, function (error) {
            _this.loading.dismiss();
        });
    };
    ResultadosDetallePage.prototype.obtenerResultado = function (resultado) {
        if (resultado == 0) {
            return 'Sitio potencialmente peligroso.';
        }
        else {
            return 'Sitio inofensivo';
        }
    };
    ResultadosDetallePage.prototype.salir = function () {
        this.viewCtrl.dismiss();
    };
    ResultadosDetallePage.prototype.showLoader = function () {
        this.loading = this.loadingCtrl.create({
            content: 'Consultando a VirusTotal...'
        });
        this.loading.present();
    };
    ResultadosDetallePage = __decorate([
        Object(__WEBPACK_IMPORTED_MODULE_0__angular_core__["m" /* Component */])({
            selector: 'page-resultados-detalle',template:/*ion-inline-start:"C:\Users\chint\Documents\phishingApp\src\pages\resultados-detalle\resultados-detalle.html"*/'<!--\n  Generated template for the ResultadosDetallePage page.\n\n  See http://ionicframework.com/docs/components/#navigation for more info on\n  Ionic pages and navigation.\n-->\n<ion-header>\n  <ion-navbar color="dark">\n    <ion-buttons right>\n      <button ion-button icon-only (click)="salir()">\n        <ion-icon name="close"></ion-icon>\n      </button>\n    </ion-buttons>\n    <ion-title>Detalle</ion-title>\n  </ion-navbar>\n</ion-header>\n\n<ion-content padding text-center>\n  <h1>\n    {{ url }}\n  </h1>\n  <br />\n  <p>\n    <b>\n      Predicción #1:\n    </b>\n    {{ obtenerResultado(resultado?.naive) }} &nbsp;&nbsp;\n    <ion-icon\n      color="danger"\n      *ngIf="resultado?.naive == 0"\n      ios="ios-alert"\n      md="md-alert"\n    ></ion-icon>\n    <ion-icon\n      color="secondary"\n      *ngIf="resultado?.naive != 0"\n      ios="ios-checkmark-circle"\n      md="md-checkmark-circle"\n    ></ion-icon>\n  </p>\n  <p>\n    <b>\n      Predicción #2:\n    </b>\n    {{ obtenerResultado(resultado?.knn) }} &nbsp;&nbsp;\n    <ion-icon\n      color="danger"\n      *ngIf="resultado?.knn == 0"\n      ios="ios-alert"\n      md="md-alert"\n    ></ion-icon>\n    <ion-icon\n      color="secondary"\n      *ngIf="resultado?.knn != 0"\n      ios="ios-checkmark-circle"\n      md="md-checkmark-circle"\n    ></ion-icon>\n  </p>\n  <p>\n    <b>\n      Predicción #3:\n    </b>\n    {{ obtenerResultado(resultado?.svc) }} &nbsp;&nbsp;\n    <ion-icon\n      color="danger"\n      *ngIf="resultado?.svc == 0"\n      ios="ios-alert"\n      md="md-alert"\n    ></ion-icon>\n    <ion-icon\n      color="secondary"\n      *ngIf="resultado?.svc != 0"\n      ios="ios-checkmark-circle"\n      md="md-checkmark-circle"\n    ></ion-icon>\n  </p>\n  <br />\n  <hr />\n  <br />\n  <h3 text-center>\n    <b>\n      Resultado obtenido por Virus Total:\n    </b>\n  </h3>\n  <p text-center>\n    {{ resultadoVT?.resultado }}\n  </p>\n</ion-content>\n'/*ion-inline-end:"C:\Users\chint\Documents\phishingApp\src\pages\resultados-detalle\resultados-detalle.html"*/
        }),
        __metadata("design:paramtypes", [__WEBPACK_IMPORTED_MODULE_1_ionic_angular__["h" /* NavController */],
            __WEBPACK_IMPORTED_MODULE_1_ionic_angular__["i" /* NavParams */],
            __WEBPACK_IMPORTED_MODULE_2__providers_analisis_analisis__["a" /* AnalisisProvider */],
            __WEBPACK_IMPORTED_MODULE_1_ionic_angular__["k" /* ViewController */],
            __WEBPACK_IMPORTED_MODULE_1_ionic_angular__["f" /* LoadingController */]])
    ], ResultadosDetallePage);
    return ResultadosDetallePage;
}());

//# sourceMappingURL=resultados-detalle.js.map

/***/ })

});
//# sourceMappingURL=1.js.map