package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityEditarPerfilBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class EditarPerfilActivity extends AppCompatActivity {

    ActivityEditarPerfilBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityEditarPerfilBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.btn_editar_perfil);

        binding.form.tilPassword.setHint(R.string.form_hint_password_opcional);

        Intent intent = getIntent();

        binding.form.etUsername.setText(intent.getStringExtra("username"));
        binding.form.etNome.setText(intent.getStringExtra("nome"));
        binding.form.etEmail.setText(intent.getStringExtra("email"));
        binding.form.etTelemovel.setText(intent.getStringExtra("telemovel"));


        // TODO: PASSWORD COM MINIMO DE 8 CHAR

        // Guardar alterações
        binding.btnGuardar.setOnClickListener(v -> {

        });

        // Cancelar
        binding.btnCancelar.setOnClickListener(v -> {finish();});
    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!ConnectionUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}