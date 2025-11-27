package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityConfiguracoesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

public class ConfiguracoesActivity extends AppCompatActivity {

    ActivityConfiguracoesBinding binding;
    PreferencesManager preferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityConfiguracoesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.configuracoes);

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Obter URL da API
        binding.etApiHost.setText(preferences.getApiHost());
        binding.etApiPath.setText(preferences.getApiPath());

        // Botão Guardar
        binding.btnGuardar.setOnClickListener(v -> {
            preferences.setApiUrl(
                String.valueOf(binding.etApiHost.getText()),
                String.valueOf(binding.etApiPath.getText())
            );
            finish();
        });

        // Botão Restaurar
        binding.btnRestaurar.setOnClickListener(v -> {
            preferences.resetApiUrl();
            binding.etApiHost.setText(preferences.getApiHost());
            binding.etApiPath.setText(preferences.getApiPath());
        });

    }

    @Override
    public void onResume()
    {
        super.onResume();
        if (!NetworkUtils.hasInternet(this)) {
            Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}