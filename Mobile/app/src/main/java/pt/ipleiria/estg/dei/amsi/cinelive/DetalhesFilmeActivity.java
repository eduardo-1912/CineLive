package pt.ipleiria.estg.dei.amsi.cinelive;

import android.os.Bundle;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySignupBinding;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityDetalhesFilmeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Seta voltar atrás
        setSupportActionBar(binding.includeToolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        getSupportActionBar().setTitle("TÍTULO DO FILME......");

    }

    // Voltar atrás
    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }

}