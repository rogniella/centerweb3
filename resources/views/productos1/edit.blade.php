<div class="card bg-gradient-info collapsed-card">
    <div class="card-header">
        <h3 class="card-title">Editar</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('user.update', 0) }}" method="POST">
            @method('PUT')
            @csrf
            <input id="id" type="number" name="id">
            <div class="form-group">
                <label class="form-control-label" for="name">Nombre</label>
                <input id="name" type="text" class="form-control" name="name">
            </div>
            <div class="form-group">
                <label class="form-control-label" for="email">E-mail</label>
                <input id="email" type="email" class="form-control" name="email">
            </div>
            <div class="form-group">
                <label class="form-control-label" for="password">Contraseña</label>
                <input type="password" class="form-control" name="password">
            </div>
            <div class="card-footer">
            <button type="submit" class="btn btn-block bg-gradient-secondary"><strong>Actualizar</strong></button>
            </div>
        </form>
    </div>
</div>