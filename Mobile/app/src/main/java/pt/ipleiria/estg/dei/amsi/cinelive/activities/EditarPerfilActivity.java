package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.os.Bundle;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityEditarPerfilBinding;

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

        // Seta voltar atrás
        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setTitle(R.string.btn_editar_perfil);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);


        // Colocar hint password com (opcional)
        binding.form.tilPassword.setHint(R.string.form_hint_password_opcional);

        // TODO: PASSWORD COM MINIMO DE 8 CHAR

        binding.btnCancelar.setOnClickListener(v -> {
            finish();
        });

    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}