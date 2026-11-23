<div class="modal fade" id="modalRangosCristal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-adjust"></i> Administrar rangos <span id="rangosProductoDescripcion"></span></h4>
                <p class="text-muted" style="margin-bottom: 0;">
                    <span id="rangosProductoDescripcion"></span>
                    (<span id="rangosProductoCodigo"></span>)
                </p>
            </div>
            <div class="modal-body">
                <div id="mensajeRangosCristal" class="alert alert-danger" style="display: none;"></div>

                <div class="table-responsive">
                    <table class="table table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Esferico</th>
                                <th>Esferico Menor</th>
                                <th>Cilindro</th>
                                <th>Cilindro Menor</th>
                                <th>Add</th>
                                <th>Rango</th>
                                <th>Método</th>
                                <th class="text-right">Opciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoRangosCristal"></tbody>
                    </table>
                </div>

                <hr>

                <form id="formRangoCristal" autocomplete="off">
                    <input type="hidden" id="rangoCristalId">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_esf">Esferico</label>
                                <input type="number" step="0.25" class="form-control" id="Cri_esf" name="Cri_esf">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_esf_neg">Esferico Menor</label>
                                <input type="number" step="0.25" class="form-control" id="Cri_esf_neg" name="Cri_esf_neg">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_cil">Cilindro</label>
                                <input type="number" step="0.25" class="form-control" id="Cri_cil" name="Cri_cil">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_cil_neg">Cilindro Menor</label>
                                <input type="number" step="0.25" class="form-control" id="Cri_cil_neg" name="Cri_cil_neg">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_add">Add</label>
                                <input type="number" step="0.25" class="form-control" id="Cri_add" name="Cri_add">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_rango">Rango</label>
                                <input type="number" step="1" class="form-control" id="Cri_rango" name="Cri_rango">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="Cri_metodo">Método</label>
                                <input type="number" step="1" class="form-control" id="Cri_metodo" name="Cri_metodo">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="btnNuevoRangoCristal">Nuevo rango</button>
                <button type="button" class="btn btn-primary" id="btnGuardarRangoCristal">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
